<?php

namespace Modules\AppRssSchedules\Console;

use Illuminate\Console\Command;
use Modules\AppRssSchedules\Models\RssSchedule;
use Modules\AppRssSchedules\Models\RssScheduleHistory;
use Modules\AppChannels\Models\Accounts;
use Modules\AppRssSchedules\Facades\RssAutomation;
use Modules\AdminUsers\Models\Teams;
use App\Models\User;
use Publishing;
use Modules\AdminCrons\Facades\CronService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;

class RssCronJobCommand extends Command
{
    protected $signature = 'apprsschedules:cron';
    protected $description = 'Execute RSS schedule posts and import to history';

    public function handle()
    {
        $now = time();

        // Get all schedules that are due to run
        $schedules = RssSchedule::where('status', 1)
            //->where('next_try', '<=', $now)
            ->get();

        if ($schedules->isEmpty()) {
            CronService::notify('No RSS schedule to process.', 'info', $this);
            return 0;
        }

        foreach ($schedules as $schedule) {
            $accounts = $schedule->accounts ?? [];
            if (is_string($accounts)) {
                $accounts = json_decode($accounts, true);
                if (!is_array($accounts)) $accounts = [];
            }
            if (empty($accounts)) continue;

            $data = is_array($schedule->data) ? $schedule->data : json_decode($schedule->data, true);
            $time_posts = $data['time_posts'] ?? [];
            $weekdays   = $data['weekdays'] ?? [];

            $didPost = false; // Đánh dấu có đăng bài nào không
            try {
                $feed = RssAutomation::fetchRSS($schedule->url);
            } catch (\Exception $e) {
                CronService::notify("Failed to fetch RSS: " . $schedule->url . " - " . $e->getMessage(), 'error', $this);
                continue;
            }
            if (empty($feed['items'])) continue;

            foreach ($accounts as $accountId) {
                $account = Accounts::find($accountId);
                if (!$account) continue;

                $usedLinks = RssScheduleHistory::where('schedule_id', $schedule->id)
                    ->where('account_id', $accountId)
                    ->pluck('post_link')
                    ->toArray();

                foreach ($feed['items'] as $item) {
                    if (in_array($item['link'], $usedLinks)) continue;

                    $postData = (object)[
                        "id_secure"       => rand_string(),
                        "team_id"         => $schedule->team_id,
                        "account_id"      => $account->id,
                        "social_network"  => $account->social_network,
                        "category"        => $account->category,
                        "module"          => $account->module,
                        "function"        => "post",
                        "api_type"        => $account->login_type,
                        "type"            => "link",
                        "method"          => "rss",
                        "query_id"        => $schedule->id,
                        "data"            => json_encode([
                            "caption" => $item['desc'],
                            "link"    => $item['link'],
                            "title"   => $item['title'],
                            "medias"  => $item['image'] ? [$item['image']] : [],
                        ]),
                        "time_post"        => time(),
                        "delay"            => 0,
                        "repost_frequency" => 0,
                        "result"           => "",
                        "status"           => 3,
                        "changed"          => time(),
                        "created"          => time(),
                    ];

                    $validator = Publishing::validate([$postData]);
                    $canPost = json_decode($validator["can_post"]);

                    if (!empty($canPost) || $validator["status"] === "success") {
                        $result = Publishing::post([$postData], $canPost);

                        RssScheduleHistory::create([
                            'schedule_id'   => $schedule->id,
                            'account_id'    => $accountId,
                            'post_link'     => $item['link'],
                            'post_title'    => $item['title'],
                            'published_at'  => $item['created'] ?? time(),
                            'created'       => time(),
                        ]);

                        $didPost = true;
                    }
                    break;
                }
            }

            $schedule->changed = time();
            if ($didPost) {
                $schedule->time_post = time();
            }

            $team = Teams::find($schedule->team_id);
            $userTimezone = $team?->owner ? User::find($team->owner)?->timezone : config('app.timezone', 'UTC');

            // Always update next_try
            $nextTry = $this->getNextTime($time_posts, $weekdays, $userTimezone);

            // Kiểm tra end_date
            $end_date = $data['end_date'] ?? null;
            if ($end_date && $nextTry > $end_date) {
                $schedule->status = 0; 
            } else {
                $schedule->next_try = $nextTry;
            }

            $schedule->save();
        }

        CronService::notify("RSS schedule cron completed!", 'info', $this);
        return 0;
    }

    /**
     * Calculate the next run time based on time_posts and weekdays.
     * @param array $timePosts   Time slots ["08:00", "15:30", ...]
     * @param array $weekdays    Enabled weekdays ["Mon" => 1, ...]
     * @return int               Next run timestamp
     */
    private function getNextTime(array $timePosts, array $weekdays, string $timezone = 'UTC'): int
    {
        $now = Carbon::now($timezone);

        if (empty($timePosts) || empty($weekdays)) {
            return $now->timestamp;
        }

        // Chuẩn hoá timePosts
        $timePosts = array_map(fn($t) => date("H:i", strtotime($t)), $timePosts);
        sort($timePosts);

        // Ép weekdays về int (0/1)
        $weekdays = array_map('intval', $weekdays); 
        // ex: ["Mon" => 0, "Tue" => 0, "Wed" => 1, ...]

        // Duyệt 14 ngày tới
        for ($i = 0; $i < 14; $i++) {
            $day = $now->copy()->startOfDay()->addDays($i);
            $dayName = $day->format('D'); // "Mon", "Tue", ...

            if (!empty($weekdays[$dayName])) { // chỉ lấy ngày có bật
                foreach ($timePosts as $tp) {
                    [$h, $m] = explode(':', $tp);
                    $slot = $day->copy()->setTime((int)$h, (int)$m);

                    if ($slot->greaterThan($now)) {
                        // trả về UTC timestamp để lưu DB
                        return $slot->clone()->setTimezone('UTC')->timestamp;
                    }
                }
            }
        }

        // fallback: sau 5 phút nữa
        return $now->addMinutes(3600)->setTimezone('UTC')->timestamp;
    }
}
