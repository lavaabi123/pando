<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $brandName }} - {{ __('Analytics Report') }}</title>
    <style>
        @font-face {
            font-family: 'NotoSans';
            src: url("{{ base_path('resources/fonts/NotoSans-Regular.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'NotoSans';
            src: url("{{ base_path('resources/fonts/NotoSans-Bold.ttf') }}") format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'NotoSans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 30px 20px;
        }
        .header {
            text-align: right;
            margin-bottom: 30px;
            font-size: 9px;
            color: #888;
        }
        .brand-name {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin-bottom: 3px;
        }
        h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 25px;
            color: #000;
        }
        .social-section {
            margin-bottom: 35px;
            page-break-inside: avoid;
        }
        .social-header {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
            color: #222;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 15px;
        }
        .metric-card {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 10px;
            background: #fafafa;
        }
        .metric-label {
            font-size: 8px;
            color: #666;
            margin-bottom: 4px;
        }
        .metric-value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        .metric-change {
            font-size: 8px;
            margin-top: 2px;
        }
        .metric-change.positive {
            color: #22c55e;
        }
        .metric-change.negative {
            color: #ef4444;
        }
        .chart-container {
            margin-top: 12px;
            page-break-inside: avoid;
        }
        .chart-img {
            width: 100%;
            height: auto;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        .account-info {
            font-size: 9px;
            color: #666;
            margin-bottom: 8px;
        }
        .account-name {
            font-weight: bold;
            color: #000;
        }
        .date-range {
            font-size: 8px;
            color: #888;
            margin-bottom: 3px;
        }
        .page-break {
            page-break-after: always;
        }
        .summary-section {
            background: #f5f5f5;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 25px;
        }
        .summary-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #000;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item-label {
            font-size: 8px;
            color: #666;
            margin-bottom: 3px;
        }
        .summary-item-value {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }
        .summary-item-change {
            font-size: 7px;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand-name">{{ $brandName }}</div>
        <div class="date-range">{{ $startDate }} - {{ $endDate }}</div>
    </div>

    {{-- Overall Summary --}}
    @php
        $totalFollowers = 0;
        $platformCount = 0;
        foreach ($analyticsData as $social => $accounts) {
            foreach ($accounts as $data) {
                $platformCount++;
                $overview = $data['analytics']['overview'] ?? [];
                $totalFollowers += $overview['followers'] ?? 0;
            }
        }
    @endphp

    <div class="summary-section">
        <div class="summary-title">{{ __('Overall Summary') }}</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-item-label">{{ __('Total Platforms') }}</div>
                <div class="summary-item-value">{{ $platformCount }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-label">{{ __('Total Followers') }}</div>
                <div class="summary-item-value">{{ number_format($totalFollowers) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-label">{{ __('Date Range') }}</div>
                <div class="summary-item-value">{{ Carbon\Carbon::parse($startDate)->format('M d') }} - {{ Carbon\Carbon::parse($endDate)->format('M d, Y') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-label">{{ __('Generated') }}</div>
                <div class="summary-item-value">{{ $generatedAt }}</div>
            </div>
        </div>
    </div>

    {{-- Facebook Section --}}
    @if (isset($analyticsData['facebook']))
        <div class="social-section">
            <h2 class="social-header">Facebook</h2>
            @foreach ($analyticsData['facebook'] as $data)
                @php
                    $account = $data['account'];
                    $analytics = $data['analytics'];
                    $overview = $analytics['overview'] ?? [];
                @endphp
                
                <div class="account-info">
                    <span class="account-name">{{ $account->username }}</span>
                    <span class="date-range">({{ $startDate }} - {{ $endDate }})</span>
                </div>

                <div class="metrics-grid">
                    {{-- Followers --}}
                    <div class="metric-card">
                        <div class="metric-label">Followers</div>
                        <div class="metric-value">{{ number_format($overview['followers'] ?? 0) }}</div>
                        @if (isset($overview['followers_change']))
                            <div class="metric-change {{ ($overview['followers_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['followers_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['followers_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Page Views --}}
                    <div class="metric-card">
                        <div class="metric-label">Page Views</div>
                        <div class="metric-value">{{ number_format($overview['page_views'] ?? 0) }}</div>
                        @if (isset($overview['page_views_change']))
                            <div class="metric-change {{ ($overview['page_views_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['page_views_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['page_views_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Post Engagement --}}
                    <div class="metric-card">
                        <div class="metric-label">Post Engagement</div>
                        <div class="metric-value">{{ number_format($overview['post_engagement'] ?? 0) }}</div>
                        @if (isset($overview['post_engagement_change']))
                            <div class="metric-change {{ ($overview['post_engagement_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['post_engagement_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['post_engagement_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Chart if available --}}
                @if (!empty($analytics['fan_history_chart']['chartImage']))
                    <div class="chart-container">
                        <img src="{{ $analytics['fan_history_chart']['chartImage'] }}" alt="Facebook Chart" class="chart-img">
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Instagram Section --}}
    @if (isset($analyticsData['instagram']))
        <div class="social-section">
            <h2 class="social-header">Instagram</h2>
            @foreach ($analyticsData['instagram'] as $data)
                @php
                    $account = $data['account'];
                    $analytics = $data['analytics'];
                    $overview = $analytics['overview'] ?? [];
                @endphp
                
                <div class="account-info">
                    <span class="account-name">{{ $account->username }}</span>
                    <span class="date-range">({{ $startDate }} - {{ $endDate }})</span>
                </div>

                <div class="metrics-grid">
                    {{-- Followers --}}
                    <div class="metric-card">
                        <div class="metric-label">Followers</div>
                        <div class="metric-value">{{ number_format($overview['followers'] ?? 0) }}</div>
                        @if (isset($overview['followers_change']))
                            <div class="metric-change {{ ($overview['followers_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['followers_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['followers_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Likes --}}
                    <div class="metric-card">
                        <div class="metric-label">Likes</div>
                        <div class="metric-value">{{ number_format($overview['likes'] ?? 0) }}</div>
                        @if (isset($overview['likes_change']))
                            <div class="metric-change {{ ($overview['likes_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['likes_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['likes_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Views --}}
                    <div class="metric-card">
                        <div class="metric-label">Views</div>
                        <div class="metric-value">{{ number_format($overview['views'] ?? 0) }}</div>
                        @if (isset($overview['views_change']))
                            <div class="metric-change {{ ($overview['views_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['views_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['views_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Chart if available --}}
                @if (!empty($analytics['followers_chart']['chartImage']))
                    <div class="chart-container">
                        <img src="{{ $analytics['followers_chart']['chartImage'] }}" alt="Instagram Chart" class="chart-img">
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- TikTok Section --}}
    @if (isset($analyticsData['tiktok']))
        <div class="social-section">
            <h2 class="social-header">TikTok</h2>
            @foreach ($analyticsData['tiktok'] as $data)
                @php
                    $account = $data['account'];
                    $analytics = $data['analytics'];
                    $overview = $analytics['overview'] ?? [];
                @endphp
                
                <div class="account-info">
                    <span class="account-name">{{ $account->username }}</span>
                    <span class="date-range">({{ $startDate }} - {{ $endDate }})</span>
                </div>

                <div class="metrics-grid">
                    {{-- Shares --}}
                    <div class="metric-card">
                        <div class="metric-label">Shares</div>
                        <div class="metric-value">{{ number_format($overview['shares'] ?? 0) }}</div>
                        @if (isset($overview['shares_change']))
                            <div class="metric-change {{ ($overview['shares_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['shares_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['shares_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Total Followers --}}
                    <div class="metric-card">
                        <div class="metric-label">Total Followers (All Time)</div>
                        <div class="metric-value">{{ number_format($overview['total_followers'] ?? 0) }}</div>
                        @if (isset($overview['total_followers_change']))
                            <div class="metric-change {{ ($overview['total_followers_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['total_followers_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['total_followers_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Likes --}}
                    <div class="metric-card">
                        <div class="metric-label">Likes</div>
                        <div class="metric-value">{{ number_format($overview['likes'] ?? 0) }}</div>
                        @if (isset($overview['likes_change']))
                            <div class="metric-change {{ ($overview['likes_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['likes_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['likes_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Profile Views --}}
                    <div class="metric-card">
                        <div class="metric-label">Profile Views</div>
                        <div class="metric-value">{{ number_format($overview['profile_views'] ?? 0) }}</div>
                        @if (isset($overview['profile_views_change']))
                            <div class="metric-change {{ ($overview['profile_views_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['profile_views_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['profile_views_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Video Views --}}
                    <div class="metric-card">
                        <div class="metric-label">Video Views</div>
                        <div class="metric-value">{{ number_format($overview['video_views'] ?? 0) }}</div>
                        @if (isset($overview['video_views_change']))
                            <div class="metric-change {{ ($overview['video_views_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['video_views_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['video_views_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Chart if available --}}
                @if (!empty($analytics['followers_chart']['chartImage']))
                    <div class="chart-container">
                        <img src="{{ $analytics['followers_chart']['chartImage'] }}" alt="TikTok Chart" class="chart-img">
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- YouTube Section --}}
    @if (isset($analyticsData['youtube']))
        <div class="social-section">
            <h2 class="social-header">YouTube</h2>
            @foreach ($analyticsData['youtube'] as $data)
                @php
                    $account = $data['account'];
                    $analytics = $data['analytics'];
                    $overview = $analytics['overview'] ?? [];
                @endphp
                
                <div class="account-info">
                    <span class="account-name">{{ $account->username }}</span>
                    <span class="date-range">({{ $startDate }} - {{ $endDate }})</span>
                </div>

                <div class="metrics-grid">
                    {{-- Subscribers Gained --}}
                    <div class="metric-card">
                        <div class="metric-label">YouTube Subscribers Gained</div>
                        <div class="metric-value">{{ number_format($overview['subscribers_gained'] ?? 0) }}</div>
                        @if (isset($overview['subscribers_gained_change']))
                            <div class="metric-change {{ ($overview['subscribers_gained_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['subscribers_gained_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['subscribers_gained_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Video Views --}}
                    <div class="metric-card">
                        <div class="metric-label">Video Views</div>
                        <div class="metric-value">{{ number_format($overview['video_views'] ?? 0) }}</div>
                        @if (isset($overview['video_views_change']))
                            <div class="metric-change {{ ($overview['video_views_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['video_views_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['video_views_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Likes --}}
                    <div class="metric-card">
                        <div class="metric-label">YouTube Likes</div>
                        <div class="metric-value">{{ number_format($overview['likes'] ?? 0) }}</div>
                        @if (isset($overview['likes_change']))
                            <div class="metric-change {{ ($overview['likes_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['likes_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['likes_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Views --}}
                    <div class="metric-card">
                        <div class="metric-label">YouTube Views</div>
                        <div class="metric-value">{{ number_format($overview['views'] ?? 0) }}</div>
                        @if (isset($overview['views_change']))
                            <div class="metric-change {{ ($overview['views_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['views_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['views_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Chart if available --}}
                @if (!empty($analytics['subscribers_chart']['chartImage']))
                    <div class="chart-container">
                        <img src="{{ $analytics['subscribers_chart']['chartImage'] }}" alt="YouTube Chart" class="chart-img">
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- LinkedIn Section --}}
    @if (isset($analyticsData['linkedin']))
        <div class="social-section">
            <h2 class="social-header">LinkedIn</h2>
            @foreach ($analyticsData['linkedin'] as $data)
                @php
                    $account = $data['account'];
                    $analytics = $data['analytics'];
                    $overview = $analytics['overview'] ?? [];
                @endphp
                
                <div class="account-info">
                    <span class="account-name">{{ $account->username }}</span>
                    <span class="date-range">({{ $startDate }} - {{ $endDate }})</span>
                </div>

                <div class="metrics-grid">
                    {{-- Followers --}}
                    <div class="metric-card">
                        <div class="metric-label">Followers</div>
                        <div class="metric-value">{{ number_format($overview['followers'] ?? 0) }}</div>
                        @if (isset($overview['followers_change']))
                            <div class="metric-change {{ ($overview['followers_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['followers_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['followers_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Impressions --}}
                    <div class="metric-card">
                        <div class="metric-label">Impressions</div>
                        <div class="metric-value">{{ number_format($overview['impressions'] ?? 0) }}</div>
                        @if (isset($overview['impressions_change']))
                            <div class="metric-change {{ ($overview['impressions_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['impressions_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['impressions_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Engagement --}}
                    <div class="metric-card">
                        <div class="metric-label">Engagement</div>
                        <div class="metric-value">{{ number_format($overview['engagement'] ?? 0) }}</div>
                        @if (isset($overview['engagement_change']))
                            <div class="metric-change {{ ($overview['engagement_change'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                {{ ($overview['engagement_change'] ?? 0) >= 0 ? '▲' : '▼' }} 
                                {{ abs($overview['engagement_change_percent'] ?? 0) }}%
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Chart if available --}}
                @if (!empty($analytics['followers_chart']['chartImage']))
                    <div class="chart-container">
                        <img src="{{ $analytics['followers_chart']['chartImage'] }}" alt="LinkedIn Chart" class="chart-img">
                    </div>
                @endif
            @endforeach
        </div>
    @endif

</body>
</html>
