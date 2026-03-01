<?php

namespace Tests\Feature;

use App\Models\ForumCategory;
use App\Models\ForumFlag;
use App\Models\ForumLog;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;
use App\Services\Forum\ForumService;
use App\Services\Forum\ModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * ForumTest
 *
 * Production-grade feature tests covering all critical forum paths:
 *   1. Thread creation stores correct data and generates a slug
 *   2. Nested reply depth is enforced (only depth-0 parents allowed)
 *   3. Only one official answer exists per thread at a time
 *   4. Locked threads reject new replies
 *   5. Banned users cannot create threads or replies
 *   6. XSS payloads are stripped from thread body and reply body
 *   7. Duplicate flagging is rejected; reply status becomes 'flagged'
 *   8. Thread slugs are globally unique even for identical titles
 *   9. Vendors are denied thread creation (policy / model helper)
 *  10. High-volume paginated thread listings are correct
 */
class ForumTest extends TestCase
{
    use RefreshDatabase;

    // ── Service singletons resolved from the container ────────────────────────

    private ForumService $forum;
    private ModerationService $moderation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->forum      = $this->app->make(ForumService::class);
        $this->moderation = $this->app->make(ModerationService::class);

        // Fake all mail/database notifications — avoids SMTP + mail queue errors
        Notification::fake();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Create a standard farmer (role = user) who can post. */
    private function farmer(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    /** Create an admin user. */
    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** Create an expert user. */
    private function expert(): User
    {
        return User::factory()->create(['role' => 'expert']);
    }

    /** Create a vendor user. */
    private function vendor(): User
    {
        return User::factory()->create(['role' => 'vendor']);
    }

    /** Return a category, creating one if none exists. */
    private function category(): ForumCategory
    {
        return ForumCategory::factory()->create();
    }

    // =========================================================================
    // 1. Thread Creation
    // =========================================================================

    /**
     * @test
     */
    public function test_authenticated_user_can_create_a_thread(): void
    {
        $author   = $this->farmer();
        $category = $this->category();

        $thread = $this->forum->createThread($author, [
            'title'             => 'My Test Thread Title',
            'body'              => '<p>Hello world</p>',
            'forum_category_id' => $category->id,
        ]);

        // Thread is persisted
        $this->assertDatabaseHas('forum_threads', [
            'id'                => $thread->id,
            'user_id'           => $author->id,
            'title'             => 'My Test Thread Title',
            'forum_category_id' => $category->id,
            'status'            => ForumThread::STATUS_OPEN,
        ]);

        // Slug is generated from title
        $this->assertNotEmpty($thread->slug);
        $this->assertStringContainsString('my-test-thread-title', $thread->slug);

        // Audit log entry is created
        $this->assertDatabaseHas('forum_logs', [
            'user_id' => $author->id,
            'action'  => ForumLog::ACTION_THREAD_CREATE,
            'thread_id' => $thread->id,
        ]);
    }

    // =========================================================================
    // 2. Nested Reply Depth Enforcement
    // =========================================================================

    /**
     * @test
     */
    public function test_replying_to_a_top_level_reply_is_allowed(): void
    {
        $author = $this->farmer();
        $thread = ForumThread::factory()->create(['status' => ForumThread::STATUS_OPEN]);

        // Top-level reply (depth 0)
        $topLevel = $this->forum->createReply($author, $thread, [
            'body'      => 'Top level reply',
            'parent_id' => null,
        ]);

        $this->assertNull($topLevel->parent_id);

        // Child reply (depth 1 — allowed)
        $child = $this->forum->createReply($author, $thread, [
            'body'      => 'Child reply',
            'parent_id' => $topLevel->id,
        ]);

        $this->assertEquals($topLevel->id, $child->parent_id);
    }

    /**
     * @test
     */
    public function test_replying_to_a_child_reply_throws_domain_exception(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Maximum reply nesting depth exceeded.');

        $author = $this->farmer();
        $thread = ForumThread::factory()->create(['status' => ForumThread::STATUS_OPEN]);

        // Create depth-0 reply
        $topLevel = ForumReply::factory()->create([
            'thread_id' => $thread->id,
            'parent_id' => null,
        ]);

        // Create depth-1 reply (child)
        $child = ForumReply::factory()->create([
            'thread_id' => $thread->id,
            'parent_id' => $topLevel->id,
        ]);

        // Attempt depth-2 reply — must throw
        $this->forum->createReply($author, $thread, [
            'body'      => 'Illegal grandchild reply',
            'parent_id' => $child->id,
        ]);
    }

    // =========================================================================
    // 3. Official Answer Uniqueness
    // =========================================================================

    /**
     * @test
     */
    public function test_marking_second_reply_as_official_clears_first(): void
    {
        $expert = $this->expert();
        $thread = ForumThread::factory()->create(['status' => ForumThread::STATUS_OPEN]);

        $replyA = ForumReply::factory()->create(['thread_id' => $thread->id, 'is_official' => false]);
        $replyB = ForumReply::factory()->create(['thread_id' => $thread->id, 'is_official' => false]);

        // Mark reply A as official
        $this->forum->markOfficialAnswer($expert, $replyA);
        $this->assertDatabaseHas('forum_replies', ['id' => $replyA->id, 'is_official' => true]);

        // Mark reply B as official — A must lose official status
        $this->forum->markOfficialAnswer($expert, $replyB);
        $this->assertDatabaseHas('forum_replies', ['id' => $replyB->id, 'is_official' => true]);
        $this->assertDatabaseHas('forum_replies', ['id' => $replyA->id, 'is_official' => false]);

        // Exactly one official reply exists on this thread
        $officialCount = ForumReply::where('thread_id', $thread->id)
            ->where('is_official', true)
            ->count();

        $this->assertEquals(1, $officialCount);
    }

    /**
     * @test
     */
    public function test_marking_official_on_locked_thread_throws_exception(): void
    {
        $this->expectException(\DomainException::class);

        $expert = $this->expert();
        $thread = ForumThread::factory()->locked()->create();
        $reply  = ForumReply::factory()->create(['thread_id' => $thread->id]);

        $this->forum->markOfficialAnswer($expert, $reply);
    }

    // =========================================================================
    // 4. Locked Thread Prevents Replies
    // =========================================================================

    /**
     * @test
     */
    public function test_creating_reply_on_locked_thread_is_rejected_by_policy(): void
    {
        $user   = $this->farmer();
        $thread = ForumThread::factory()->locked()->create();

        // The ForumReplyPolicy::create() returns false if thread is locked/archived
        $this->assertFalse(
            $user->can('create', [ForumReply::class, $thread]),
            'Policy should deny reply creation on a locked thread.'
        );
    }

    /**
     * @test
     */
    public function test_creating_reply_on_archived_thread_is_rejected_by_policy(): void
    {
        $user   = $this->farmer();
        $thread = ForumThread::factory()->archived()->create();

        $this->assertFalse(
            $user->can('create', [ForumReply::class, $thread]),
            'Policy should deny reply creation on an archived thread.'
        );
    }

    /**
     * @test
     */
    public function test_admin_can_always_post_regardless_of_thread_status(): void
    {
        $admin  = $this->admin();
        $thread = ForumThread::factory()->locked()->create();

        // Admin bypasses policy via before() hook
        $this->assertTrue(
            $admin->can('create', [ForumReply::class, $thread]),
            'Admin should bypass locked-thread policy via before() hook.'
        );
    }

    // =========================================================================
    // 5. Banned User Cannot Post
    // =========================================================================

    /**
     * @test
     */
    public function test_permanently_banned_user_cannot_create_thread(): void
    {
        $user = $this->farmer();
        $user->update(['is_banned' => true, 'banned_until' => null]);

        $this->assertTrue($user->isCurrentlyBanned());

        // Policy check: create returns false for a banned user
        $this->assertFalse(
            $user->can('create', ForumThread::class),
            'Permanently banned user should be denied thread creation.'
        );
    }

    /**
     * @test
     */
    public function test_temporarily_banned_user_is_denied_during_ban_period(): void
    {
        $user = $this->farmer();
        $user->update([
            'is_banned'    => true,
            'banned_until' => now()->addHours(24),
        ]);

        $this->assertTrue($user->isCurrentlyBanned());
        $this->assertFalse($user->can('create', ForumThread::class));
    }

    /**
     * @test
     */
    public function test_temporary_ban_expires_and_user_can_post_again(): void
    {
        $user = $this->farmer();
        $user->update([
            'is_banned'    => true,
            'banned_until' => now()->subMinute(),  // expired 1 minute ago
        ]);

        // isCurrentlyBanned() checks expiry — user should NOT be banned
        $this->assertFalse(
            $user->isCurrentlyBanned(),
            'Expired ban should not block the user.'
        );

        $this->assertTrue($user->can('create', ForumThread::class));
    }

    /**
     * @test
     */
    public function test_banned_user_cannot_create_reply(): void
    {
        $user   = $this->farmer();
        $thread = ForumThread::factory()->create(['status' => ForumThread::STATUS_OPEN]);
        $reply  = ForumReply::factory()->make(['thread_id' => $thread->id]);

        $user->update(['is_banned' => true, 'banned_until' => null]);

        $this->assertFalse(
            $user->can('create', [ForumReply::class, $thread]),
            'Banned user should be denied reply creation.'
        );
    }

    // =========================================================================
    // 6. XSS Sanitization
    // =========================================================================

    /**
     * @test
     */
    public function test_script_tag_is_stripped_from_thread_body(): void
    {
        $author   = $this->farmer();
        $category = $this->category();

        $thread = $this->forum->createThread($author, [
            'title'             => 'XSS Test Thread',
            'body'              => '<p>Hello</p><script>alert("xss")</script>',
            'forum_category_id' => $category->id,
        ]);

        $stored = ForumThread::find($thread->id)->body;
        $this->assertStringNotContainsString('<script>', $stored);
        $this->assertStringNotContainsString('alert(', $stored);
    }

    /**
     * @test
     */
    public function test_on_event_handler_is_stripped_from_reply_body(): void
    {
        $author = $this->farmer();
        $thread = ForumThread::factory()->create(['status' => ForumThread::STATUS_OPEN]);

        $reply = $this->forum->createReply($author, $thread, [
            'body'      => '<p onmouseover="alert(1)">hover me</p>',
            'parent_id' => null,
        ]);

        $stored = ForumReply::find($reply->id)->body;
        $this->assertStringNotContainsString('onmouseover', $stored);
        $this->assertStringNotContainsString('alert(', $stored);
    }

    /**
     * @test
     */
    public function test_legitimate_html_tags_are_preserved_in_thread_body(): void
    {
        $author   = $this->farmer();
        $category = $this->category();

        $thread = $this->forum->createThread($author, [
            'title'             => 'Safe HTML Thread',
            'body'              => '<p>A list:</p><ul><li>Item 1</li></ul><strong>Bold</strong>',
            'forum_category_id' => $category->id,
        ]);

        $stored = ForumThread::find($thread->id)->body;
        $this->assertStringContainsString('<ul>', $stored);
        $this->assertStringContainsString('<strong>', $stored);
    }

    // =========================================================================
    // 7. Duplicate Flag Rejection + Reply Status
    // =========================================================================

    /**
     * @test
     */
    public function test_flagging_a_reply_sets_its_status_to_flagged(): void
    {
        $reporter = $this->farmer();
        $thread   = ForumThread::factory()->create();
        $reply    = ForumReply::factory()->create([
            'thread_id' => $thread->id,
            'status'    => ForumReply::STATUS_VISIBLE,
        ]);

        $this->forum->flagReply($reporter, $reply, 'spam');

        $this->assertDatabaseHas('forum_replies', [
            'id'     => $reply->id,
            'status' => ForumReply::STATUS_FLAGGED,
        ]);

        $this->assertDatabaseHas('forum_flags', [
            'reply_id'   => $reply->id,
            'flagged_by' => $reporter->id,
            'reason'     => 'spam',
            'status'     => ForumFlag::STATUS_PENDING,
        ]);
    }

    /**
     * @test
     */
    public function test_same_user_cannot_flag_a_reply_twice(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('You have already flagged this reply.');

        $reporter = $this->farmer();
        $thread   = ForumThread::factory()->create();
        $reply    = ForumReply::factory()->create(['thread_id' => $thread->id]);

        // First flag — succeeds
        $this->forum->flagReply($reporter, $reply, 'spam');

        // Second flag from same user — must throw
        $this->forum->flagReply($reporter, $reply, 'harassment');
    }

    /**
     * @test
     */
    public function test_multiple_different_users_can_flag_the_same_reply(): void
    {
        $thread  = ForumThread::factory()->create();
        $reply   = ForumReply::factory()->create(['thread_id' => $thread->id]);

        $userA = $this->farmer();
        $userB = $this->farmer();

        $this->forum->flagReply($userA, $reply, 'spam');
        $this->forum->flagReply($userB, $reply->fresh(), 'misinformation');

        $this->assertEquals(
            2,
            ForumFlag::where('reply_id', $reply->id)->count()
        );
    }

    // =========================================================================
    // 8. Slug Uniqueness
    // =========================================================================

    /**
     * @test
     */
    public function test_identical_titles_produce_distinct_slugs(): void
    {
        $author   = $this->farmer();
        $category = $this->category();
        $data     = [
            'title'             => 'My Duplicate Title',
            'body'              => '<p>Body text</p>',
            'forum_category_id' => $category->id,
        ];

        $threadA = $this->forum->createThread($author, $data);
        $threadB = $this->forum->createThread($author, $data);
        $threadC = $this->forum->createThread($author, $data);

        $slugs = [$threadA->slug, $threadB->slug, $threadC->slug];
        $this->assertCount(3, array_unique($slugs), 'All three slugs must be unique.');
    }

    /**
     * @test
     */
    public function test_slug_is_url_safe_and_lowercase(): void
    {
        $author   = $this->farmer();
        $category = $this->category();

        $thread = $this->forum->createThread($author, [
            'title'             => 'Testing SLUG Generation 123!',
            'body'              => '<p>body</p>',
            'forum_category_id' => $category->id,
        ]);

        // Slug should be lowercase, no special characters except dashes
        $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $thread->slug);
    }

    // =========================================================================
    // 9. Vendor Permission Enforcement
    // =========================================================================

    /**
     * @test
     */
    public function test_vendor_cannot_create_a_thread_via_policy(): void
    {
        $vendor = $this->vendor();

        $this->assertFalse(
            $vendor->canCreateForumThread(),
            'canCreateForumThread() must return false for vendor role.'
        );

        $this->assertFalse(
            $vendor->can('create', ForumThread::class),
            'ForumThreadPolicy::create() must deny vendors.'
        );
    }

    /**
     * @test
     */
    public function test_farmer_can_create_a_thread_via_policy(): void
    {
        $farmer = $this->farmer();

        $this->assertTrue($farmer->canCreateForumThread());
        $this->assertTrue($farmer->can('create', ForumThread::class));
    }

    /**
     * @test
     */
    public function test_expert_can_create_a_thread_via_policy(): void
    {
        $expert = $this->expert();

        $this->assertTrue($expert->canCreateForumThread());
        $this->assertTrue($expert->can('create', ForumThread::class));
    }

    // =========================================================================
    // 10. High-Volume Pagination
    // =========================================================================

    /**
     * @test
     */
    public function test_thread_list_paginates_correctly_for_large_dataset(): void
    {
        $category = $this->category();

        // Create 37 approved open threads
        ForumThread::factory()->count(37)->create([
            'forum_category_id' => $category->id,
            'is_approved'       => true,
            'status'            => ForumThread::STATUS_OPEN,
        ]);

        $page = $this->forum->listThreads([]);

        // Page size is 15
        $this->assertEquals(15, $page->perPage());
        // Total is 37
        $this->assertEquals(37, $page->total());
        // 3 pages required: ceil(37/15) = 3
        $this->assertEquals(3, $page->lastPage());
        // First page has 15 items
        $this->assertCount(15, $page->items());
    }

    /**
     * @test
     */
    public function test_archived_threads_are_excluded_from_public_listing(): void
    {
        $category = $this->category();

        ForumThread::factory()->count(5)->create([
            'forum_category_id' => $category->id,
            'is_approved'       => true,
            'status'            => ForumThread::STATUS_OPEN,
        ]);

        ForumThread::factory()->count(3)->create([
            'forum_category_id' => $category->id,
            'is_approved'       => true,
            'status'            => ForumThread::STATUS_ARCHIVED,
        ]);

        $result = $this->forum->listThreads([]);

        $this->assertEquals(5, $result->total(), 'Archived threads must not appear in the public listing.');
    }

    /**
     * @test
     */
    public function test_unapproved_threads_are_excluded_from_public_listing(): void
    {
        ForumThread::factory()->count(4)->create(['is_approved' => false]);
        ForumThread::factory()->count(6)->create(['is_approved' => true, 'status' => ForumThread::STATUS_OPEN]);

        $result = $this->forum->listThreads([]);
        $this->assertEquals(6, $result->total());
    }

    /**
     * @test
     */
    public function test_pinned_threads_appear_first_in_listing(): void
    {
        $category = $this->category();

        // Create 3 normal threads
        ForumThread::factory()->count(3)->create([
            'forum_category_id' => $category->id,
            'is_approved'       => true,
            'status'            => ForumThread::STATUS_OPEN,
            'is_pinned'         => false,
        ]);

        // Create 2 pinned threads
        $pinned = ForumThread::factory()->count(2)->create([
            'forum_category_id' => $category->id,
            'is_approved'       => true,
            'status'            => ForumThread::STATUS_OPEN,
            'is_pinned'         => true,
        ]);

        $pinnedIds = $pinned->pluck('id')->toArray();
        $listing   = $this->forum->listThreads([]);
        $firstTwo  = collect($listing->items())->take(2)->pluck('id')->toArray();

        sort($pinnedIds);
        sort($firstTwo);

        $this->assertEquals($pinnedIds, $firstTwo, 'Pinned threads must be the first two results.');
    }

    // =========================================================================
    // Bonus: ModerationService — lock / unlock / resolve
    // =========================================================================

    /**
     * @test
     */
    public function test_admin_can_lock_and_unlock_a_thread(): void
    {
        $admin  = $this->admin();
        $thread = ForumThread::factory()->create(['status' => ForumThread::STATUS_OPEN]);

        $this->moderation->lockThread($admin, $thread, 'Violation of terms.');
        $this->assertEquals(ForumThread::STATUS_LOCKED, $thread->fresh()->status);

        $this->moderation->unlockThread($admin, $thread->fresh());
        $this->assertEquals(ForumThread::STATUS_OPEN, $thread->fresh()->status);
    }

    /**
     * @test
     */
    public function test_admin_can_resolve_thread_with_an_official_reply(): void
    {
        $admin  = $this->admin();
        $thread = ForumThread::factory()->create(['status' => ForumThread::STATUS_OPEN]);
        $reply  = ForumReply::factory()->create(['thread_id' => $thread->id]);

        $this->moderation->resolveThread($admin, $thread, $reply);

        $fresh = $thread->fresh();
        $this->assertEquals(ForumThread::STATUS_RESOLVED, $fresh->status);
        $this->assertEquals($reply->id, $fresh->resolved_reply_id);
        $this->assertTrue((bool) ForumReply::find($reply->id)->is_official);
    }

    /**
     * @test
     */
    public function test_resolving_thread_with_wrong_reply_throws_exception(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Reply does not belong to the specified thread.');

        $admin    = $this->admin();
        $thread   = ForumThread::factory()->create();
        $otherThread = ForumThread::factory()->create();
        $wrongReply  = ForumReply::factory()->create(['thread_id' => $otherThread->id]);

        $this->moderation->resolveThread($admin, $thread, $wrongReply);
    }

    /**
     * @test
     */
    public function test_admin_cannot_be_banned(): void
    {
        $this->expectException(\DomainException::class);

        $admin  = $this->admin();
        $target = $this->admin();  // another admin

        $this->moderation->banUser($admin, $target, 'Test', null, false);
    }

    /**
     * @test
     */
    public function test_ban_user_and_unban_clears_all_fields(): void
    {
        $admin  = $this->admin();
        $victim = $this->farmer();

        $this->moderation->banUser($admin, $victim, 'Spam', now()->addDays(3), false);

        $victim->refresh();
        $this->assertTrue($victim->is_banned);
        $this->assertNotNull($victim->banned_until);

        $this->moderation->unbanUser($admin, $victim);

        $victim->refresh();
        $this->assertFalse($victim->is_banned);
        $this->assertNull($victim->banned_until);
        $this->assertNull($victim->banned_reason);
    }

    // =========================================================================
    // Bonus: Reply edit window enforcement
    // =========================================================================

    /**
     * @test
     */
    public function test_reply_can_be_edited_within_15_minute_window(): void
    {
        $user   = $this->farmer();
        $thread = ForumThread::factory()->create();
        $reply  = ForumReply::factory()->create(['thread_id' => $thread->id, 'user_id' => $user->id]);

        $updated = $this->forum->editReply($reply, '<p>Updated body</p>');

        $this->assertStringContainsString('Updated body', $updated->body);
        $this->assertNotNull($updated->edited_at);
    }

    /**
     * @test
     */
    public function test_reply_cannot_be_edited_after_15_minute_window(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Edit window has expired.');

        $user   = $this->farmer();
        $thread = ForumThread::factory()->create();
        $reply  = ForumReply::factory()->create([
            'thread_id'  => $thread->id,
            'user_id'    => $user->id,
            'created_at' => now()->subMinutes(20),
        ]);

        $this->forum->editReply($reply, '<p>Too late</p>');
    }
}
