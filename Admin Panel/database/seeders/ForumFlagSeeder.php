<?php

namespace Database\Seeders;

use App\Models\ForumFlag;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ForumFlagSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $adminId = DB::table('users')->where('email', 'admin@gmail.com')->value('id')
            ?? DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');

        $reporterIds = DB::table('users')
            ->where('role', 'user')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $replies = DB::table('forum_replies')
            ->join('forum_threads', 'forum_replies.thread_id', '=', 'forum_threads.id')
            ->whereNull('forum_replies.parent_id')
            ->orderBy('forum_replies.id')
            ->select([
                'forum_replies.id as reply_id',
                'forum_replies.thread_id',
                'forum_threads.title as thread_title',
            ])
            ->get();

        if ($adminId === null || $replies->isEmpty() || empty($reporterIds)) {
            return;
        }

        $definitions = [
            [
                'reply_index' => 0,
                'reporter_index' => 0,
                'reason' => 'Possible overstatement of fungicide dosage.',
                'status' => ForumFlag::STATUS_PENDING,
            ],
            [
                'reply_index' => 1,
                'reporter_index' => 1,
                'reason' => 'Advice needs review for crop stage accuracy.',
                'status' => ForumFlag::STATUS_RESOLVED,
            ],
            [
                'reply_index' => 2,
                'reporter_index' => 2,
                'reason' => 'Promotional language looks like spam.',
                'status' => ForumFlag::STATUS_IGNORED,
            ],
            [
                'reply_index' => 3,
                'reporter_index' => 3,
                'reason' => 'Potentially unsafe herbicide guidance.',
                'status' => ForumFlag::STATUS_PENDING,
            ],
            [
                'reply_index' => 4,
                'reporter_index' => 4,
                'reason' => 'Reply appears outdated after recent price changes.',
                'status' => ForumFlag::STATUS_RESOLVED,
            ],
            [
                'reply_index' => 5,
                'reporter_index' => 5,
                'reason' => 'Off-topic personal comment.',
                'status' => ForumFlag::STATUS_IGNORED,
            ],
        ];

        foreach ($definitions as $definition) {
            $reply = $replies->get($definition['reply_index']);
            $reporterId = $reporterIds[$definition['reporter_index'] % count($reporterIds)];

            $payload = [
                'reason' => $definition['reason'],
                'status' => $definition['status'],
                'reviewed_at' => in_array($definition['status'], [ForumFlag::STATUS_RESOLVED, ForumFlag::STATUS_IGNORED], true) ? $now : null,
                'reviewed_by' => in_array($definition['status'], [ForumFlag::STATUS_RESOLVED, ForumFlag::STATUS_IGNORED], true) ? $adminId : null,
            ];

            DB::table('forum_flags')->updateOrInsert(
                [
                    'reply_id' => $reply->reply_id,
                    'flagged_by' => $reporterId,
                ],
                $payload + [
                    'created_at' => $now->copy()->subDays(rand(1, 45)),
                ]
            );
        }
    }
}