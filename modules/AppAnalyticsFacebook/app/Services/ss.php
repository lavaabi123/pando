<?php

namespace Modules\AppAnalyticsFacebook\Services;

use Modules\AppAnalytics\Models\SocialAnalytics;
use Modules\AppAnalytics\Models\SocialAnalyticsSnapshot;
use Modules\AppAnalytics\Models\SocialAnalyticsPost;
use Modules\AppAnalytics\Models\SocialAnalyticsPostInfo;
use Modules\AppAnalytics\Models\SocialAnalyticsLog;
use Modules\AppAnalytics\Contracts\SocialAnalyticsInterface;
use JanuSoftware\Facebook\Facebook;
use Carbon\Carbon;
use DB;

class FacebookAnalytics implements SocialAnalyticsInterface
{
    protected Facebook $fb;

    public function __construct()
    {
        try {
            $this->fb = $this->getFacebookClient();
        } catch (\Exception $e) {}
    }

    protected function getFacebookClient(): Facebook
    {
        return new Facebook([
            'app_id'                => get_option('facebook_client_id', ''),
            'app_secret'            => get_option('facebook_client_secret', ''),
            'default_graph_version' => config('facebook.default_graph_version', 'v21.0'),
            'default_access_token'  => get_option('facebook_client_token', ''),
        ]);
    }

    public function getAnalyticsData(int $teamId, ?string $id_secure = null, ?string $since = null, ?string $until = null): array
    {
        $accountInfo = $this->getAccountInfo($teamId, $id_secure);
        if (!$accountInfo) {
            return [
                'status' => 'error',
                'message' => __('Facebook account not found or disconnected.'),
            ];
        }

        $accountId = $accountInfo['id'];
        $pageId = $accountInfo['pid'];

        //SYNC NEW DATA
        $account = \Modules\AppChannels\Models\Accounts::find($accountId);
        $this->syncPageInsights($accountId, $pageId, $account->token, $since, $until);
        $this->syncPostInsights($accountId, $pageId, $account->token, $since, $until);

        $overview = $this->getFacebookOverview($accountId, $since, $until);
        $overviewChart = $this->getOverviewChartData($accountId, $since, $until);
        $dailyPageViewsChartData = $this->getDailyPageViewsChartData($accountId, $since, $until);
        $fanHistoryChartData = $this->getFanHistoryChartData($accountId, $since, $until);
        $fansChartData = $this->getFanChangesChartData($accountId, $since, $until);
        $fanSummary = $this->getFanSummary($accountId, $since, $until);
        $postReachSummaryChart = $this->getPostReachSummaryChartData($accountId, $since, $until);
        $postImpressionSummaryChart = $this->getPostImpressionSummaryChartData($accountId, $since, $until);
        $postEngagementSummaryChart = $this->getPostEngagementSummaryChartData($accountId, $since, $until);
        $postEngagementRateSummaryData = $this->getPostEngagementRateSummaryData($accountId, $since, $until);
        $videoViewCompletionChart = $this->getVideoViewCompletionChartData($accountId, $since, $until);
        $videoOrganicPaidChart = $this->getVideoOrganicPaidChartData($accountId, $since, $until);
        $videoPlayMethodChart = $this->getVideoPlayMethodChartData($accountId, $since, $until);
        $postHistoryList = $this->getPostHistoryList($accountId, $since, $until);
        $fansLocationMapChart = $this->getFansLocationMapChartData($accountId, $since, $until);
        $topFansCountries = $this->getTopFansCountries($accountId, $since, $until);

        return [
            'status' => 'success',
            'account' => $accountInfo,
            'account_id' => $accountId,
            'account_id_secure' => $id_secure,
            'start_date' => $since,
            'end_date' => $until,
            'social' => 'facebook',
            'overview' => $overview,
            'overview_chart' => $overviewChart,
            'fan_summary' => $fanSummary,
            'fan_history_chart' => $fanHistoryChartData,
            'gained_lost_fans_chart' => $fansChartData,
            'page_views_chart' => $dailyPageViewsChartData,
            'postReachSummaryChart' => $postReachSummaryChart,
            'postImpressionSummaryChart' => $postImpressionSummaryChart,
            'postEngagementSummaryChart' => $postEngagementSummaryChart,
            'postEngagementRateSummary' => $postEngagementRateSummaryData,
            'videoViewCompletionChart' => $videoViewCompletionChart,
            'videoOrganicPaidChart' => $videoOrganicPaidChart,
            'videoPlayMethodChart' => $videoPlayMethodChart,
            'postHistoryList' => $postHistoryList,
            'fansLocationMapChart' => $fansLocationMapChart,
            'topFansCountries' => $topFansCountries
        ];
    }

    protected function getAccountInfo(int $teamId, ?string $id_secure = null): ?array
    {
        $query = \Modules\AppChannels\Models\Accounts::where('team_id', $teamId)
            ->where('social_network', 'facebook');

        if ($id_secure) {
            $query->where('id_secure', $id_secure);
        }

        $account = $query->first();

        if (!$account) {
            return null;
        }

        // Get current followers count from page
        try {
            $endpoint = "/{$account->pid}?fields=followers_count,fan_count,name,category";
            $response = $this->fb->get($endpoint, $account->token);
            $pageData = $response->getDecodedBody();

            return [
                'id' => $account->id,
                'pid' => $account->pid,
                'name' => $pageData['name'] ?? $account->name,
                'followers_count' => $pageData['followers_count'] ?? 0,
                'fan_count' => $pageData['fan_count'] ?? 0,
                'category' => $pageData['category'] ?? '',
                'avatar' => $account->avatar,
            ];
        } catch (\Exception $e) {
            logger()->error("[FacebookAnalytics] Could not fetch page info: " . $e->getMessage());
            return [
                'id' => $account->id,
                'pid' => $account->pid,
                'name' => $account->name,
                'followers_count' => 0,
                'fan_count' => 0,
                'category' => '',
                'avatar' => $account->avatar,
            ];
        }
    }

    protected function syncPageInsights(int $accountId, string $pageId, string $token, string $since, string $until): void
    {
        $social = 'facebook';

        // UPDATED: Only valid metrics that work with period=day
        $metrics = [
            'page_impressions_unique',
            'page_impressions_paid_unique',
            'page_actions_post_reactions_total',
            'page_post_engagements',
            'page_views_total',
            'page_follows',
            'page_video_complete_views_30s_organic',
            'page_video_views_organic',
            'page_video_views_paid',
            'page_video_views_autoplayed',
            'page_video_views_click_to_play',
        ];

        if (!\Analytics::shouldFetchSocialAnalytics($accountId, $social, 'page')) {
            return;
        }

        try {
            $endpoint = "/{$pageId}/insights?metric=" . implode(',', $metrics) .
                "&since={$since}&until={$until}&period=day";

            $response = $this->fb->get($endpoint, $token);
            $result = $response->getDecodedBody();

            $insights = [];

            foreach ($result['data'] ?? [] as $item) {
                $metric = $item['name'];
                foreach ($item['values'] as $entry) {
                    $date = Carbon::parse($entry['end_time'])->toDateString();

                    if (is_array($entry['value'])) {
                        foreach ($entry['value'] as $key => $count) {
                            if ((float) $count > 0) {
                                $insights["{$metric}.{$key}"][$date] = (float) $count;
                            }
                        }
                    } else {
                        $value = (float) $entry['value'];
                        if ($value > 0) {
                            $insights[$metric][$date] = $value;
                        }
                    }
                }
            }

            // Calculate organic reach (total - paid)
            if (isset($insights['page_impressions_unique'])) {
                foreach ($insights['page_impressions_unique'] as $date => $totalReach) {
                    $paidReach = $insights['page_impressions_paid_unique'][$date] ?? 0;
                    $organicReach = $totalReach - $paidReach;
                    if ($organicReach > 0) {
                        $insights['page_impressions_organic_unique'][$date] = $organicReach;
                    }
                }
            }

            // Get followers count from page object
            $this->fetchPageFansCount($pageId, $token, $insights);

            if (!empty($insights)) {
                \Analytics::saveInsightsToDatabase($accountId, $social, $insights);
                \Analytics::markSynced($accountId, $social, 'page');
            }

        } catch (\Exception $e) {
            logger()->error("[FacebookAnalytics] syncPageInsights error: " . $e->getMessage(), [
                'account_id' => $accountId,
                'page_id' => $pageId,
            ]);
        }
    }

    protected function fetchPageFansCount(string $pageId, string $token, array &$insights): void
    {
        try {
            $endpoint = "/{$pageId}?fields=followers_count,fan_count";
            $response = $this->fb->get($endpoint, $token);
            $result = $response->getDecodedBody();

            $date = Carbon::now()->toDateString();

            if (isset($result['followers_count'])) {
                $insights['page_followers_count'][$date] = (float) $result['followers_count'];
            }

            if (isset($result['fan_count'])) {
                $insights['page_fan_count'][$date] = (float) $result['fan_count'];
            }

        } catch (\Exception $e) {
            logger()->warning("[FacebookAnalytics] Could not fetch page fans count: " . $e->getMessage());
        }
    }

    protected function syncPostInsights(int $accountId, string $pageId, string $token, string $since, string $until): void
    {
        // Your existing post insights logic
    }

    protected function getFacebookOverview(int $accountId, string $since, string $until): array
    {
        $dateRange = [$since, $until];

        // UPDATED: Calculate organic reach from valid metrics
        $totalReach = \Analytics::getMetricSum($accountId, 'facebook', 'page_impressions_unique', $dateRange);
        $paidReach = \Analytics::getMetricSum($accountId, 'facebook', 'page_impressions_paid_unique', $dateRange);
        $organicReach = max(0, $totalReach - $paidReach);

        return [
            'total_reach' => $totalReach,
            'organic_reach' => $organicReach,
            'paid_reach' => $paidReach,
            'engagements' => \Analytics::getMetricSum($accountId, 'facebook', 'page_post_engagements', $dateRange),
            'reactions' => \Analytics::getMetricSum($accountId, 'facebook', 'page_actions_post_reactions_total', $dateRange),
            'page_views' => \Analytics::getMetricSum($accountId, 'facebook', 'page_views_total', $dateRange),
            'new_follows' => \Analytics::getMetricSum($accountId, 'facebook', 'page_follows', $dateRange),
            'video_views_organic' => \Analytics::getMetricSum($accountId, 'facebook', 'page_video_views_organic', $dateRange),
            'video_views_paid' => \Analytics::getMetricSum($accountId, 'facebook', 'page_video_views_paid', $dateRange),
            'video_complete_views' => \Analytics::getMetricSum($accountId, 'facebook', 'page_video_complete_views_30s_organic', $dateRange),
            'current_followers' => \Analytics::getLatestMetric($accountId, 'facebook', 'page_followers_count') ?? 0,
        ];
    }

    protected function getOverviewChartData(int $accountId, string $since, string $until): array
    {
        // UPDATED: Use valid metrics
        $metrics = [
            'page_impressions_unique' => __('Unique Reach'),
            'page_post_engagements' => __('Engagements'),
            'page_views_total' => __('Page Views'),
        ];

        $chartData = [
            'labels' => [],
            'datasets' => [],
        ];

        foreach ($metrics as $metric => $label) {
            $data = \Analytics::getMetricByDateRange($accountId, 'facebook', $metric, $since, $until);

            if (empty($chartData['labels']) && !empty($data)) {
                $chartData['labels'] = array_keys($data);
            }

            $chartData['datasets'][] = [
                'label' => $label,
                'data' => array_values($data),
            ];
        }

        return $chartData;
    }

    protected function getDailyPageViewsChartData(int $accountId, string $since, string $until): array
    {
        $data = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_views_total', $since, $until);

        return [
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => __('Page Views'),
                    'data' => array_values($data),
                ]
            ]
        ];
    }

    protected function getFanHistoryChartData(int $accountId, string $since, string $until): array
    {
        // UPDATED: Use page_followers_count from page object
        $data = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_followers_count', $since, $until);

        return [
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => __('Total Followers'),
                    'data' => array_values($data),
                ]
            ]
        ];
    }

    protected function getFanChangesChartData(int $accountId, string $since, string $until): array
    {
        // UPDATED: Use page_follows instead of page_fan_adds_unique
        $newFollows = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_follows', $since, $until);

        return [
            'labels' => array_keys($newFollows),
            'datasets' => [
                [
                    'label' => __('New Follows'),
                    'data' => array_values($newFollows),
                ]
            ]
        ];
    }

    protected function getFanSummary(int $accountId, string $since, string $until): array
    {
        $dateRange = [$since, $until];

        return [
            'new_follows' => \Analytics::getMetricSum($accountId, 'facebook', 'page_follows', $dateRange),
            'current_followers' => \Analytics::getLatestMetric($accountId, 'facebook', 'page_followers_count') ?? 0,
        ];
    }

    protected function getPostReachSummaryChartData(int $accountId, string $since, string $until): array
    {
        // UPDATED: Use calculated organic reach
        $organicReach = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_impressions_organic_unique', $since, $until);
        $paidReach = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_impressions_paid_unique', $since, $until);

        $labels = array_unique(array_merge(array_keys($organicReach), array_keys($paidReach)));
        sort($labels);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('Organic Reach'),
                    'data' => array_map(fn($date) => $organicReach[$date] ?? 0, $labels),
                ],
                [
                    'label' => __('Paid Reach'),
                    'data' => array_map(fn($date) => $paidReach[$date] ?? 0, $labels),
                ]
            ]
        ];
    }

    protected function getPostImpressionSummaryChartData(int $accountId, string $since, string $until): array
    {
        // UPDATED: Use page_impressions_unique as impressions metric
        $data = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_impressions_unique', $since, $until);

        return [
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => __('Unique Impressions'),
                    'data' => array_values($data),
                ]
            ]
        ];
    }

    protected function getPostEngagementSummaryChartData(int $accountId, string $since, string $until): array
    {
        $engagements = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_post_engagements', $since, $until);
        $reactions = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_actions_post_reactions_total', $since, $until);

        $labels = array_unique(array_merge(array_keys($engagements), array_keys($reactions)));
        sort($labels);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('Total Engagements'),
                    'data' => array_map(fn($date) => $engagements[$date] ?? 0, $labels),
                ],
                [
                    'label' => __('Reactions'),
                    'data' => array_map(fn($date) => $reactions[$date] ?? 0, $labels),
                ]
            ]
        ];
    }

    protected function getPostEngagementRateSummaryData(int $accountId, string $since, string $until): array
    {
        $dateRange = [$since, $until];

        $reach = \Analytics::getMetricSum($accountId, 'facebook', 'page_impressions_unique', $dateRange);
        $engagements = \Analytics::getMetricSum($accountId, 'facebook', 'page_post_engagements', $dateRange);

        $engagementRate = $reach > 0 ? ($engagements / $reach) * 100 : 0;

        return [
            'engagement_rate' => round($engagementRate, 2),
            'total_engagements' => $engagements,
            'total_reach' => $reach,
        ];
    }

    protected function getVideoViewCompletionChartData(int $accountId, string $since, string $until): array
    {
        $completeViews = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_video_complete_views_30s_organic', $since, $until);

        return [
            'labels' => array_keys($completeViews),
            'datasets' => [
                [
                    'label' => __('30s+ Complete Views'),
                    'data' => array_values($completeViews),
                ]
            ]
        ];
    }

    protected function getVideoOrganicPaidChartData(int $accountId, string $since, string $until): array
    {
        $organicViews = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_video_views_organic', $since, $until);
        $paidViews = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_video_views_paid', $since, $until);

        $labels = array_unique(array_merge(array_keys($organicViews), array_keys($paidViews)));
        sort($labels);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('Organic Video Views'),
                    'data' => array_map(fn($date) => $organicViews[$date] ?? 0, $labels),
                ],
                [
                    'label' => __('Paid Video Views'),
                    'data' => array_map(fn($date) => $paidViews[$date] ?? 0, $labels),
                ]
            ]
        ];
    }

    protected function getVideoPlayMethodChartData(int $accountId, string $since, string $until): array
    {
        $autoplayViews = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_video_views_autoplayed', $since, $until);
        $clickViews = \Analytics::getMetricByDateRange($accountId, 'facebook', 'page_video_views_click_to_play', $since, $until);

        $labels = array_unique(array_merge(array_keys($autoplayViews), array_keys($clickViews)));
        sort($labels);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('Autoplayed'),
                    'data' => array_map(fn($date) => $autoplayViews[$date] ?? 0, $labels),
                ],
                [
                    'label' => __('Click to Play'),
                    'data' => array_map(fn($date) => $clickViews[$date] ?? 0, $labels),
                ]
            ]
        ];
    }

    protected function getPostHistoryList(int $accountId, string $since, string $until): array
    {
        // Your existing implementation
        return [];
    }

    protected function getFansLocationMapChartData(int $accountId, string $since, string $until): array
    {
        // page_fans_country is deprecated, return empty
        return [];
    }

    protected function getTopFansCountries(int $accountId, string $since, string $until): array
    {
        // page_fans_country is deprecated, return empty
        return [];
    }
}