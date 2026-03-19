<?php

namespace Modules\AppChannelLinkedinProfiles\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\AppChannelLinkedinProfiles\Classes\LinkedinAPI;

class CheckLinkedinTokens extends Command
{
    protected $signature   = 'pando:check-linkedin-tokens';
    protected $description = 'Expire LinkedIn tokens that are time-expired or revoked, then notify users';

    private array $revokedPhrases = [
        'token used in the request has been revoked',
        'revoked_access_token',
        'access token expired',
        'token is expired',
        'invalid access token',
        'expired_token',
        'unauthorized',
    ];

    public function handle(): int
    {
        Log::info('LinkedinTokenCheck: ────── START ──────');

        // ── 1. Fetch active LinkedIn accounts ─────────────────────────────────
        // status = 1       → account is connected/active
        // token_status = 1 → already flagged as expired, skip
        $accounts = DB::table('accounts')
            ->where('social_network', 'linkedin')
            ->where('status', 1)
            ->where('token_status', '!=', 1)
            ->get(['id', 'brand_id', 'team_id', 'name', 'category', 'token', 'expires_at', 'changed']);

        Log::info('LinkedinTokenCheck: accounts fetched', [
            'count'    => $accounts->count(),
            'accounts' => $accounts->map(fn($a) => [
                'id'       => $a->id,
                'name'     => $a->name,
                'brand_id' => $a->brand_id,
                'team_id'  => $a->team_id,
                'token_set' => !empty($a->token),
            ])->toArray(),
        ]);

        if ($accounts->isEmpty()) {
            $this->info('No active LinkedIn accounts found.');
            Log::info('LinkedinTokenCheck: no active accounts — exiting.');
            return Command::SUCCESS;
        }

        // ── 2. Init LinkedIn API ──────────────────────────────────────────────
        $linkedin = new LinkedinAPI(
            get_option('linkedin_app_id', ''),
            get_option('linkedin_app_secret', ''),
            '', '', false
        );

        // ── 3. Check each account ─────────────────────────────────────────────
        $expiredAccounts = collect();

        foreach ($accounts as $account) {
            Log::info("LinkedinTokenCheck: → checking [{$account->id}] {$account->name}");

            $reason = $this->getExpiryReason($linkedin, $account);

            Log::info("LinkedinTokenCheck: ← result [{$account->id}] {$account->name}", [
                'reason' => $reason ?? 'VALID',
            ]);

            if ($reason) {
                $expiredAccounts->push($account);
                $this->line("  Expired [{$reason}]: {$account->name} (ID: {$account->id})");
            }
        }

        if ($expiredAccounts->isEmpty()) {
            $this->info('All LinkedIn tokens are valid.');
            Log::info('LinkedinTokenCheck: all tokens valid — exiting.');
            return Command::SUCCESS;
        }

        // ── 4. Mark as expired: token_status = 1 → needs reconnect ───────────
        $expiredIds = $expiredAccounts->pluck('id')->toArray();

        $rowsAffected = DB::table('accounts')
            ->whereIn('id', $expiredIds)
            ->update(['token_status' => 1]);

        $verifyStatuses = DB::table('accounts')
            ->whereIn('id', $expiredIds)
            ->pluck('token_status', 'id')
            ->toArray();

        Log::info('LinkedinTokenCheck: DB update done', [
            'ids'                  => $expiredIds,
            'rows_affected'        => $rowsAffected,
            'post_update_statuses' => $verifyStatuses,
        ]);

        $this->info("Marked {$expiredAccounts->count()} account(s). Rows affected: {$rowsAffected}.");

        // ── 5. Resolve users to notify ────────────────────────────────────────
        //
        // Who gets notified per expired account:
        //   (a) brands.user_id          → brand owner/creator    (lookup by brand_id)
        //   (b) user_brands.user_id     → users assigned to brand (lookup by brand_id)
        //   (c) team_members.uid        → team members            (lookup by team_id)
        //
        $brandIds = $expiredAccounts->pluck('brand_id')->filter()->unique()->toArray();
        $teamIds  = $expiredAccounts->pluck('team_id')->filter()->unique()->toArray();

        // (a) brands.id → user_id  (key = brand_id)
        $brandOwners = DB::table('brands')
            ->whereIn('id', $brandIds)
            ->pluck('user_id', 'id');

        // (b) user_brands: brand_id → user_id
        $brandMembers = DB::table('user_brands')
            ->whereIn('brand_id', $brandIds)
            ->get(['user_id', 'brand_id']);

        // (c) team_members: team_id → uid
        $teamMembers = DB::table('team_members')
            ->whereIn('team_id', $teamIds)
            ->where('status', 1)
            ->get(['uid', 'team_id']);

        // Brand names for email: brand_id → brand name
        $brandNames = DB::table('brands')
            ->whereIn('id', $brandIds)
            ->pluck('name', 'id');

        Log::info('LinkedinTokenCheck: user resolution', [
            'brand_ids'     => $brandIds,
            'team_ids'      => $teamIds,
            'brand_owners'  => $brandOwners->toArray(),
            'brand_members' => $brandMembers->map(fn($r) => ['user_id' => $r->user_id, 'brand_id' => $r->brand_id])->toArray(),
            'team_members'  => $teamMembers->map(fn($r) => ['uid' => $r->uid, 'team_id' => $r->team_id])->toArray(),
            'brand_names'   => $brandNames->toArray(),
        ]);

        // Build: user_id → [accounts]  (grouped so one email per user)
        $userAccountMap = [];

        foreach ($expiredAccounts as $account) {
            $bid   = $account->brand_id;
            $tid   = $account->team_id;
            $users = [];

            // (a) brand owner
            if ($bid && isset($brandOwners[$bid])) {
                $users[] = (int) $brandOwners[$bid];
            }

            // (b) users assigned to this brand
            foreach ($brandMembers->where('brand_id', $bid) as $r) {
                $users[] = (int) $r->user_id;
            }

            // (c) team members
            foreach ($teamMembers->where('team_id', $tid) as $r) {
                $users[] = (int) $r->uid;
            }

            foreach (array_unique($users) as $userId) {
                $userAccountMap[$userId][] = $account;
            }
        }

        Log::info('LinkedinTokenCheck: users to notify', ['user_ids' => array_keys($userAccountMap)]);

        if (empty($userAccountMap)) {
            $this->warn('No users found to notify.');
            Log::warning('LinkedinTokenCheck: userAccountMap empty — check brands/user_brands/team_members.');
            return Command::SUCCESS;
        }

        $users = DB::table('users')
            ->whereIn('id', array_keys($userAccountMap))
            ->where('status', 2)
            ->whereNotNull('email')
            ->get(['id', 'fullname', 'email'])
            ->keyBy('id');

        // ── 6. Email (one per user) + in-app notification ─────────────────────
        foreach ($userAccountMap as $userId => $accs) {
            $user = $users->get($userId);

            if (!$user) {
                Log::warning("LinkedinTokenCheck: user {$userId} not found/inactive — skipping.");
                continue;
            }

            // Build accounts_list — one line per account, includes brand name
            $accountsList = collect($accs)
                ->map(function ($a) use ($brandNames) {
                    $brand    = $brandNames[$a->brand_id] ?? null;
                    $category = ucfirst($a->category ?? 'profile');
                    $line     = '• ' . $a->name . ' (' . $category . ')';
                    if ($brand) {
                        $line .= ' — ' . $brand;
                    }
                    return $line;
                })
                ->implode("\n");

            // ── Email via MailSender template ─────────────────────────────────
            try {
                \MailSender::sendByTemplate('linkedin_token_expired', $user->email, [
                    'fullname'      => $user->fullname,
                    'accounts_list' => $accountsList,
                    'reconnect_url' => url_app('channels'),
                ]);

                Log::info("LinkedinTokenCheck: email sent", ['to' => $user->email]);
                $this->info("  Email sent → {$user->email}");
            } catch (\Throwable $e) {
                Log::error('LinkedinTokenCheck: email failed', [
                    'user'  => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            // ── In-app notification per expired account ───────────────────────
            foreach ($accs as $account) {
                try {
                    $brand   = $brandNames[$account->brand_id] ?? null;
                    $label   = ucfirst($account->category ?? 'profile');
                    $message = $brand
                        ? __('Your LinkedIn :label ":name" under ":brand" has been disconnected. Please reconnect it.', [
                            'label' => $label,
                            'name'  => $account->name,
                            'brand' => $brand,
                          ])
                        : __('Your LinkedIn :label ":name" has been disconnected. Please reconnect it to continue publishing.', [
                            'label' => $label,
                            'name'  => $account->name,
                          ]);

                    \Notifier::create($userId, $message, url_app('channels'));

                    Log::info("LinkedinTokenCheck: in-app notif sent", [
                        'user_id'    => $userId,
                        'account_id' => $account->id,
                        'brand'      => $brand,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('LinkedinTokenCheck: notifier failed', [
                        'user_id' => $userId,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info('Done. ' . count($userAccountMap) . ' user(s) notified.');
        Log::info('LinkedinTokenCheck: ────── END ──────');
        return Command::SUCCESS;
    }

    private function getExpiryReason(LinkedinAPI $linkedin, object $account): ?string
    {
        // (a) Time-based — only when expires_at is a real timestamp
        $expiresAt = trim((string) $account->expires_at);

        if ($expiresAt !== '' && is_numeric($expiresAt)) {
            $secondsLeft = ((int)$expiresAt + (int)$account->changed) - time();

            Log::info("LinkedinTokenCheck: time check [{$account->id}]", [
                'expires_at'   => $expiresAt,
                'changed'      => $account->changed,
                'seconds_left' => $secondsLeft,
            ]);

            if ($secondsLeft < 59) {
                return 'time_expired';
            }
        } else {
            Log::info("LinkedinTokenCheck: [{$account->id}] expires_at empty — API ping only");
        }

        // (b) API ping — catches revoked tokens regardless of time
        if (empty($account->token)) {
            Log::warning("LinkedinTokenCheck: [{$account->id}] no token stored.");
            return 'no_token';
        }

        try {
            Log::info("LinkedinTokenCheck: API ping [{$account->id}]");
            $response = $linkedin->getPerson($account->token);
            Log::info("LinkedinTokenCheck: API response [{$account->id}]", ['response' => $response]);

            if (isset($response['sub'])) {
                return null; // valid
            }

            $errorMsg = strtolower(
                $response['message'] ?? $response['error_description'] ?? $response['error'] ?? ''
            );

            foreach ($this->revokedPhrases as $phrase) {
                if (str_contains($errorMsg, $phrase)) {
                    Log::info("LinkedinTokenCheck: [{$account->id}] matched: '{$phrase}'");
                    return 'revoked';
                }
            }

            Log::warning("LinkedinTokenCheck: [{$account->id}] unrecognised response — not marking expired", [
                'response' => $response,
            ]);

        } catch (\Throwable $e) {
            Log::error('LinkedinTokenCheck: API ping exception', [
                'account_id' => $account->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return null;
    }
}
