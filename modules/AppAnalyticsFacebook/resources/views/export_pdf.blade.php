<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Facebook Analytics Report') }}</title>
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

        body {
            font-family: 'NotoSans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #222;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #3b5998;
            color: #3b5998;
        }

        h3 {
            font-size: 15px;
            margin-bottom: 8px;
            margin-top: 28px;
            color: #333;
        }

        .section {
            margin-bottom: 32px;
        }

        .metrics-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .metric-row {
            display: table-row;
        }

        .metric-cell {
            display: table-cell;
            padding: 12px;
            border: 1px solid #ddd;
            width: 33.33%;
            vertical-align: top;
        }

        .metric-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 4px;
        }

        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #000;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }

        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        img.chart {
            display: block;
            margin: 0 auto;
            max-width: 95%;
            margin-top: 12px;
            margin-bottom: 30px;
        }

        .highlight {
            font-weight: bold;
            color: #000;
        }

        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <h1>{{ __('Facebook Analytics Report') }}</h1>

    <div class="info-box">
        <strong>{{ __('Page Name') }}:</strong> <span class="highlight">{{ $analytics['account']['name'] ?? '-' }}</span><br>
        <strong>{{ __('Followers') }}:</strong> <span class="highlight">{{ number_format($analytics['account']['followers_count'] ?? 0) }}</span><br>
        <strong>{{ __('Category') }}:</strong> <span class="highlight">{{ $analytics['account']['category'] ?? '-' }}</span><br>
        @if (!empty($startDate) && !empty($endDate))
        <br>
        <strong>{{ __('Report Period') }}:</strong> {{ $startDate }} {{ __('to') }} {{ $endDate }}
        @endif
    </div>

    <!-- Key Metrics Overview -->
    <div class="section">
        <h3>{{ __('Key Performance Metrics') }}</h3>
        <div class="metrics-grid">
            <div class="metric-row">
                <div class="metric-cell">
                    <div class="metric-label">{{ __('Unique Reach') }}</div>
                    <div class="metric-value">{{ number_format($analytics['metrics']['page_impressions_unique'] ?? 0) }}</div>
                </div>
                <div class="metric-cell">
                    <div class="metric-label">{{ __('Paid Reach') }}</div>
                    <div class="metric-value">{{ number_format($analytics['metrics']['page_impressions_paid_unique'] ?? 0) }}</div>
                </div>
                <div class="metric-cell">
                    <div class="metric-label">{{ __('Total Engagements') }}</div>
                    <div class="metric-value">{{ number_format($analytics['metrics']['page_post_engagements'] ?? 0) }}</div>
                </div>
            </div>
            <div class="metric-row">
                <div class="metric-cell">
                    <div class="metric-label">{{ __('Total Reactions') }}</div>
                    <div class="metric-value">{{ number_format($analytics['metrics']['page_actions_post_reactions_total'] ?? 0) }}</div>
                </div>
                <div class="metric-cell">
                    <div class="metric-label">{{ __('Page Views') }}</div>
                    <div class="metric-value">{{ number_format($analytics['metrics']['page_views_total'] ?? 0) }}</div>
                </div>
                <div class="metric-cell">
                    <div class="metric-label">{{ __('New Follows') }}</div>
                    <div class="metric-value">{{ number_format($analytics['metrics']['page_follows'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($charts) && is_array($charts))
        <div class="section">
            <h3>{{ __('Performance Trends') }}</h3>
            @foreach ($charts as $chart)
                @if(isset($chart['base64']))
                    <img class="chart" src="{{ $chart['base64'] }}" alt="Chart">
                @endif
            @endforeach
        </div>
    @endif

    <!-- Daily Breakdown -->
    <div class="section page-break">
        <h3>{{ __('Daily Performance Breakdown') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Reach') }}</th>
                    <th>{{ __('Paid') }}</th>
                    <th>{{ __('Engage.') }}</th>
                    <th>{{ __('Reactions') }}</th>
                    <th>{{ __('Views') }}</th>
                    <th>{{ __('Follows') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($analytics['dailyMetrics'] ?? [] as $date => $metrics)
                    <tr>
                        <td>{{ $date }}</td>
                        <td>{{ number_format($metrics['page_impressions_unique'] ?? 0) }}</td>
                        <td>{{ number_format($metrics['page_impressions_paid_unique'] ?? 0) }}</td>
                        <td>{{ number_format($metrics['page_post_engagements'] ?? 0) }}</td>
                        <td>{{ number_format($metrics['page_actions_post_reactions_total'] ?? 0) }}</td>
                        <td>{{ number_format($metrics['page_views_total'] ?? 0) }}</td>
                        <td>{{ number_format($metrics['page_follows'] ?? 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Video Metrics (if available) -->
    @if (($analytics['metrics']['page_video_views_organic'] ?? 0) > 0 || ($analytics['metrics']['page_video_views_paid'] ?? 0) > 0)
    <div class="section">
        <h3>{{ __('Video Performance') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Metric') }}</th>
                    <th>{{ __('Value') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ __('Organic Video Views') }}</td>
                    <td class="highlight">{{ number_format($analytics['metrics']['page_video_views_organic'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td>{{ __('Paid Video Views') }}</td>
                    <td class="highlight">{{ number_format($analytics['metrics']['page_video_views_paid'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td>{{ __('Autoplayed Video Views') }}</td>
                    <td class="highlight">{{ number_format($analytics['metrics']['page_video_views_autoplayed'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td>{{ __('Click-to-Play Video Views') }}</td>
                    <td class="highlight">{{ number_format($analytics['metrics']['page_video_views_click_to_play'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td>{{ __('30s+ Complete Views (Organic)') }}</td>
                    <td class="highlight">{{ number_format($analytics['metrics']['page_video_complete_views_30s_organic'] ?? 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Summary -->
    <div class="section">
        <h3>{{ __('Summary') }}</h3>
        <p>
            {{ __('This report shows the Facebook Page performance for the selected period.') }}
            {{ __('The metrics include reach, engagement, reactions, page views, and follower growth.') }}
        </p>
        <p style="margin-top: 20px; font-size: 10px; color: #666;">
            {{ __('Generated on') }}: {{ now()->format('Y-m-d H:i:s') }}
        </p>
    </div>

</body>
</html>