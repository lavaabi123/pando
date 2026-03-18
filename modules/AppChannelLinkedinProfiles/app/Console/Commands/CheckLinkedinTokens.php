<?php

namespace Modules\AppChannelLinkedinProfiles\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\AppChannelLinkedinProfiles\Mail\LinkedinTokenExpiredMail;

class CheckLinkedinTokens extends Command
{
    protected $signature   = 'pando:check-linkedin-tokens';
    protected $description = 'Expire LinkedIn tokens that have passed their expiry and notify users';

    public function handle(): int
    {
        // ------------------------------------------------------------------
        // 1. Fetch all LinkedIn accounts whose token has expired
        //    token_status = 1 means still "active" — we only process those
        //    expires_at + changed = absolute unix expiry timestamp (CI4 pattern)
        // ------------------------------------------------------------------
        $expiredAccounts = DB::table('accounts')
            ->where('social_network', 'linkedin')
            ->where('token_status', 1)
            ->whereRaw('(expires_at + changed) < ?', [time()])
            ->get(['id', 'team_id', 'name', 'category', 'social_network']);

        if ($expiredAccounts->isEmpty()) {
            $this->info('No expired LinkedIn tokens found.');
            return Command::SUCCESS;
        }

        // ------------------------------------------------------------------
        // 2. Mark all expired accounts in one query, collect IDs
        // ------------------------------------------------------------------
        $expiredIds = $expiredAccounts->pluck('id')->toArray();

        DB::table('accounts')
            ->whereIn('id', $expiredIds)
            ->update(['token_status' => 0]);

        $this->info('Marked ' . count($expiredIds) . ' account(s) as expired.');

        // ------------------------------------------------------------------
        // 3. Collect all users to notify, grouped by user_id → [accounts]
        //    For each expired account's team_id we find:
        //      (a) Brand owner  : brands.user_id WHERE team_id = ?
        //      (b) Brand members: user_brands.user_id WHERE team_id = ?
        //      (c) Team members : team_members.uid WHERE team_id = ? AND status = 1
        // ------------------------------------------------------------------
        $teamIds = $expiredAccounts->pluck('team_id')->unique()->toArray();

        // --- (a) brand owners ---
        $brandOwners = DB::table('brands')
            ->whereIn('team_id', $teamIds)
            ->pluck('user_id', 'team_id'); // team_id => user_id

        // --- (b) user_brands members ---
        $brandMembers = DB::table('user_brands')
            ->whereIn('team_id', $teamIds)
            ->get(['user_id', 'team_id']);

        // --- (c) team_members ---
        $teamMembers = DB::table('team_members')
            ->whereIn('team_id', $teamIds)
            ->where('status', 1)
            ->get(['uid', 'team_id']);

        // Build: team_id → [user_id, ...]
        $teamUserMap = [];

        foreach ($teamIds as $teamId) {
            $users = [];

            if (isset($brandOwners[$teamId])) {
                $users[] = (int) $brandOwners[$teamId];
            }

            foreach ($brandMembers->where('team_id', $teamId) as $row) {
                $users[] = (int) $row->user_id;
            }

            foreach ($teamMembers->where('team_id', $teamId) as $row) {
                $users[] = (int) $row->uid;
            }

            $teamUserMap[$teamId] = array_unique($users);
        }

        // Build: user_id → [accounts to notify about]
        $userAccountMap = []; // user_id => collect of account objects

        foreach ($expiredAccounts as $account) {
            $usersForTeam = $teamUserMap[$account->team_id] ?? [];
            foreach ($usersForTeam as $userId) {
                $userAccountMap[$userId][] = $account;
            }
        }

        if (empty($userAccountMap)) {
            $this->warn('No users found to notify.');
            return Command::SUCCESS;
        }

        // ------------------------------------------------------------------
        // 4. Load user records (email, fullname) in one query
        // ------------------------------------------------------------------
        $userIds = array_keys($userAccountMap);

        $users = DB::table('users')
            ->whereIn('id', $userIds)
            ->where('status', 2) // active users only
            ->whereNotNull('email')
            ->get(['id', 'fullname', 'email'])
            ->keyBy('id');

        // ------------------------------------------------------------------
        // 5. For each user: send ONE grouped email + one in-app notification
        //    per expired account
        // ------------------------------------------------------------------
        foreach ($userAccountMap as $userId => $accounts) {
            $user = $users->get($userId);

            if (!$user) {
                continue;
            }

            // --- Send single grouped email ---
            try {
                Mail::to($user->email)
                    ->queue(new LinkedinTokenExpiredMail($user, $accounts));

                $this->info("Email queued → {$user->email} ({$user->fullname})");
            } catch (\Exception $e) {
                \Log::error('LinkedinTokenCheck: email failed', [
                    'user'  => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            // --- In-app notification (one per expired account) ---
            foreach ($accounts as $account) {
                try {
                    $label    = ucfirst($account->category); // Profile / Page
                    $message  = __("Your LinkedIn :label \":name\" has been disconnected. Please reconnect it to continue publishing.", [
                        'label' => $label,
                        'name'  => $account->name,
                    ]);

                    // \Notifier::send() — adjust method name to match your AdminNotifications module
                    \Notifier::send($userId, $message, url_app('channels'));
                } catch (\Exception $e) {
                    \Log::error('LinkedinTokenCheck: notifier failed', [
                        'user_id' => $userId,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info('Done. ' . count($userAccountMap) . ' user(s) notified.');
        return Command::SUCCESS;
    }
}
