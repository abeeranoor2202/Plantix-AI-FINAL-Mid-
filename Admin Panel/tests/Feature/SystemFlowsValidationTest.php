<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ForumCategory;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Forum\ForumService;
use App\Services\Forum\ModerationService;
use App\Services\Shared\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SystemFlowsValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function forum_flow_user_post_expert_answer_admin_approve_works_end_to_end(): void
    {
        $forum = app(ForumService::class);
        $moderation = app(ModerationService::class);

        $user = User::factory()->create(['role' => 'user', 'active' => true]);
        $expert = User::factory()->create(['role' => 'expert', 'active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $category = ForumCategory::factory()->create();

        $thread = $forum->createThread($user, [
            'title' => 'Yellow leaves in tomato plants',
            'body' => 'Need diagnosis and fix',
            'forum_category_id' => $category->id,
        ]);

        $reply = $forum->createReply($expert, $thread, [
            'body' => 'Check nitrogen level and root stress.',
        ]);

        $forum->markOfficialAnswer($expert, $reply);
        $moderation->approveThread($admin, $thread->fresh());

        $this->assertTrue((bool) $thread->fresh()->is_approved);
        $this->assertTrue((bool) $reply->fresh()->is_official);
    }

    /** @test */
    public function order_flow_user_order_vendor_fulfill_admin_monitor_works_end_to_end(): void
    {
        $customer = User::factory()->create(['role' => 'user', 'active' => true]);
        $vendorUser = User::factory()->create(['role' => 'vendor', 'active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $vendor = Vendor::factory()->create(['author_id' => $vendorUser->id]);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $order->update(['status' => Order::STATUS_PROCESSING]);
        $order->update(['status' => Order::STATUS_DELIVERED]);

        Sanctum::actingAs($admin);
        $dashboardResponse = $this->getJson('/api/v1/dashboards/summary');

        $dashboardResponse->assertOk()->assertJsonPath('success', true);
        $this->assertSame(Order::STATUS_DELIVERED, $order->fresh()->status);
        $this->assertGreaterThanOrEqual(1, (int) data_get($dashboardResponse->json(), 'data.orders_total', 0));
    }

    /** @test */
    public function appointment_flow_user_book_expert_accept_complete_admin_monitor_works_end_to_end(): void
    {
        $appointmentService = app(AppointmentService::class);

        $customer = User::factory()->create(['role' => 'user', 'active' => true]);
        $expertUser = User::factory()->create(['role' => 'expert', 'active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $expert = \App\Models\Expert::factory()->approved()->create(['user_id' => $expertUser->id]);

        $appointment = Appointment::factory()->create([
            'user_id' => $customer->id,
            'expert_id' => $expert->id,
            'status' => Appointment::STATUS_PENDING_EXPERT_APPROVAL,
            'type' => 'online',
            'meeting_link' => 'https://meet.example.com/qa-flow',
        ]);

        $appointment = $appointmentService->updateStatus($appointment, Appointment::STATUS_CONFIRMED, $admin->id, 'Accepted');
        $appointment = $appointmentService->complete($appointment, $expertUser->id);

        Sanctum::actingAs($admin);
        $dashboardResponse = $this->getJson('/api/v1/dashboards/summary');

        $dashboardResponse->assertOk()->assertJsonPath('success', true);
        $this->assertSame(Appointment::STATUS_COMPLETED, $appointment->fresh()->status);
    }
}
