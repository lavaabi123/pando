<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Facebook Analytics Report') }}</title>
    <style>
        @font-face {
            font-family: 'DejaVuSans';
            src: url("{{ storage_path('fonts/DejaVuSans.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'DejaVuSans';
            src: url("{{ storage_path('fonts/DejaVuSans-Bold.ttf') }}") format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVuSans', sans-serif;
            background: #000000;
            color: #ffffff;
            font-size: 11px;
            line-height: 1.4;
        }

        /* Page Layout */
        .page {
            padding: 40px;
            page-break-after: always;
            min-height: 100vh;
        }

        /* Cover Page */
        .cover-page {
            display: table;
            width: 100%;
            height: 100vh;
            text-align: left;
        }

        .cover-content {
            display: table-cell;
            vertical-align: middle;
        }

        .cover-title {
            font-size: 60px;
            font-weight: 300;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .cover-title .normal {
            font-weight: 300;
            font-style: normal;
        }

        .cover-title .italic {
            font-weight: 300;
            font-style: italic;
        }

        .cover-title .highlight {
            color: #c4ff0e;
            font-weight: 700;
        }

        .cover-brand {
            font-size: 18px;
            margin-top: 40px;
            color: #999;
        }

        .cover-date {
            font-size: 28px;
            font-weight: 700;
            margin-top: 80px;
        }

        .cover-footer {
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
            font-size: 10px;
            color: #666;
        }

        .cover-footer-grid {
            display: table;
            width: 100%;
        }

        .cover-footer-cell {
            display: table-cell;
            width: 50%;
        }

        .cover-footer-cell:last-child {
            text-align: right;
        }

        /* Section Titles */
        .section-title {
            font-size: 36px;
            font-weight: 300;
            margin-bottom: 30px;
        }

        .section-title .highlight {
            color: #c4ff0e;
            font-style: italic;
        }

        .section-subtitle {
            font-size: 12px;
            color: #999;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* Stats Grid */
        .metrics-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .metric-row {
            display: table-row;
        }

        .metric-cell {
            display: table-cell;
            background: #1a1a1a;
            border: 1px solid #333;
            padding: 15px;
            width: 33.33%;
            text-align: center;
        }

        .metric-label {
            font-size: 10px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: #c4ff0e;
        }

        .metric-change {
            font-size: 11px;
            color: #c4ff0e;
            margin-top: 5px;
        }

        /* Chart Containers */
        .chart-section {
            margin: 30px 0;
            page-break-inside: avoid;
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #fff;
        }

        .chart-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .chart-row {
            display: table-row;
        }

        .chart-cell {
            display: table-cell;
            width: 48%;
            padding: 5px;
            vertical-align: top;
        }

        .chart-container {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px;
            height: 280px;
        }

        .chart-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #1a1a1a;
        }

        .data-table th {
            background: #2a2a2a;
            color: #c4ff0e;
            padding: 10px 8px;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #333;
            font-weight: 700;
        }

        .data-table td {
            padding: 8px;
            border: 1px solid #333;
            color: #fff;
            font-size: 10px;
        }

        .data-table tbody tr:nth-child(even) {
            background: #151515;
        }

        /* Video Section */
        .video-metrics {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .video-metric-item {
            display: table;
            width: 100%;
            padding: 10px 0;
            border-bottom: 1px solid #333;
        }

        .video-metric-item:last-child {
            border-bottom: none;
        }

        .video-metric-label {
            display: table-cell;
            color: #999;
            font-size: 11px;
        }

        .video-metric-value {
            display: table-cell;
            text-align: right;
            color: #c4ff0e;
            font-size: 14px;
            font-weight: 700;
        }

        /* Page Footer */
        .page-footer {
            position: absolute;
            bottom: 30px;
            left: 40px;
            right: 40px;
            font-size: 9px;
            color: #666;
        }

        .page-footer-grid {
            display: table;
            width: 100%;
        }

        .page-footer-cell {
            display: table-cell;
            width: 50%;
        }

        .page-footer-cell:last-child {
            text-align: right;
        }

        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            max-width: 120px;
            height: auto;
        }

        /* Page Info */
        .page-info {
            text-align: center;
            padding: 20px;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .page-info-item {
            display: inline-block;
            margin: 0 20px;
            font-size: 11px;
        }

        .page-info-label {
            color: #999;
            margin-right: 5px;
        }

        .page-info-value {
            color: #c4ff0e;
            font-weight: 700;
        }

        /* Summary Box */
        .summary-box {
            background: #1a1a1a;
            border-left: 4px solid #c4ff0e;
            padding: 20px;
            margin: 20px 0;
        }

        .summary-box p {
            line-height: 1.8;
            color: #ccc;
            font-size: 11px;
        }
    </style>
</head>
<body>

{{-- ========================================
     COVER PAGE
     ======================================== --}}
<div class="page cover-page">
    <div class="cover-content">
        {{-- Logo --}}
        @php
            $path = public_path('img/logo-brand-dark.png');    
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64im = 'data:image/' . $type . ';base64,' . base64_encode($data);
            } else {
                $base64im = null;
            }
        @endphp
        
        @if($base64im)
        <div style="margin-bottom: 60px;">
            <img src="{{ $base64im }}" style="width: 120px; height: auto;">
        </div>
        @endif

        <h1 class="cover-title">
            <span class="normal">Facebook</span><br>
            <span class="italic">Analytics</span><br>
            <span class="highlight">Report</span>
        </h1>

        <div class="cover-brand">
            {{ $brand_name }}
        </div>

        <div class="cover-date">
            {{ strtoupper(now()->format('F Y')) }}
        </div>
    </div>

    <div class="cover-footer">
        <div class="cover-footer-grid">
            <div class="cover-footer-cell">
                Presented by {{ config('app.name', 'Pando') }}
            </div>
            <div class="cover-footer-cell">
                {{ config('app.url', 'itspando.com') }}
            </div>
        </div>
    </div>
</div>

{{-- ========================================
     PERFORMANCE OVERVIEW
     ======================================== --}}
<div class="page">
    <h2 class="section-title">
        Performance <span class="highlight">Overview</span>
    </h2>

    {{-- Page Info --}}
    <div class="page-info">
        <div class="page-info-item">
            <span class="page-info-label">Page Name:</span>
            <span class="page-info-value">{{ $analytics['account']['name'] ?? '-' }}</span>
        </div>
        <div class="page-info-item">
            <span class="page-info-label">Followers:</span>
            <span class="page-info-value">{{ number_format($analytics['account']['followers_count'] ?? 0) }}</span>
        </div>
        <div class="page-info-item">
            <span class="page-info-label">Category:</span>
            <span class="page-info-value">{{ $analytics['account']['category'] ?? '-' }}</span>
        </div>
        @if (!empty($startDate) && !empty($endDate))
        <div class="page-info-item">
            <span class="page-info-label">Period:</span>
            <span class="page-info-value">{{ $startDate }} to {{ $endDate }}</span>
        </div>
        @endif
    </div>

    <p class="section-subtitle">
        Strong performance delivered across key engagement metrics during this reporting period. 
        Organic growth and audience interaction show positive momentum across reach, engagement, and content performance.
    </p>

    {{-- Key Metrics Grid --}}
    <div class="metrics-grid">
        @php
            $overview = $analytics['overview'];
            $metricsChunks = array_chunk($overview, 3, true);
        @endphp
        
        @foreach($metricsChunks as $chunk)
            <div class="metric-row">
                @foreach($chunk as $key => $item)
                    <div class="metric-cell">
                        <div class="metric-label">{{ __(ucwords(str_replace('_', ' ', $key))) }}</div>
                        <div class="metric-value">{{ number_format($item['value']) }}</div>
                        {{-- Optionally show change if you have previous period data
                        <div class="metric-change">+12.5%</div>
                        --}}
                    </div>
                @endforeach
                
                {{-- Fill empty cells --}}
                @for($i = count($chunk); $i < 3; $i++)
                    <div class="metric-cell" style="border: none; background: transparent;"></div>
                @endfor
            </div>
        @endforeach
    </div>

    <div class="page-footer">
        <div class="page-footer-grid">
            <div class="page-footer-cell">Facebook Analytics Report</div>
            <div class="page-footer-cell">02</div>
        </div>
    </div>
</div>

{{-- ========================================
     PERFORMANCE TRENDS (CHARTS)
     ======================================== --}}
@if (!empty($charts) && is_array($charts))
<div class="page">
    <h2 class="section-title">
        Performance <span class="highlight">Trends</span>
    </h2>

    <div class="chart-grid">
        @foreach ($charts as $index => $chart)
            @if(isset($chart['base64']) && isset($chart['id']))
                @if($index % 2 == 0)
                    <div class="chart-row">
                @endif
                
                <div class="chart-cell">
                    <div class="chart-title">{{ $chart['title'] ?? 'Chart' }}</div>
                    <div class="chart-container">
                        <img src="{{ $chart['base64'] }}" alt="{{ $chart['title'] ?? 'Chart' }}">
                    </div>
                </div>
                
                @if($index % 2 == 1 || $loop->last)
                    </div>
                @endif
            @endif
        @endforeach
    </div>

    <div class="page-footer">
        <div class="page-footer-grid">
            <div class="page-footer-cell">Facebook Analytics Report</div>
            <div class="page-footer-cell">03</div>
        </div>
    </div>
</div>
@endif

{{-- ========================================
     DAILY BREAKDOWN TABLE
     ======================================== --}}
<div class="page">
    <h2 class="section-title">
        Daily <span class="highlight">Breakdown</span>
    </h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reach</th>
                <th>Paid</th>
                <th>Engage.</th>
                <th>Reactions</th>
                <th>Views</th>
                <th>Follows</th>
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

    <div class="page-footer">
        <div class="page-footer-grid">
            <div class="page-footer-cell">Facebook Analytics Report</div>
            <div class="page-footer-cell">04</div>
        </div>
    </div>
</div>

{{-- ========================================
     VIDEO PERFORMANCE
     ======================================== --}}
@if (($analytics['videoMetrics']['page_video_views_organic'] ?? 0) > 0 || ($analytics['videoMetrics']['page_video_views_paid'] ?? 0) > 0)
<div class="page">
    <h2 class="section-title">
        Video <span class="highlight">Performance</span>
    </h2>

    <div class="video-metrics">
        <div class="video-metric-item">
            <div class="video-metric-label">Organic Video Views</div>
            <div class="video-metric-value">{{ number_format($analytics['videoMetrics']['page_video_views_organic'] ?? 0) }}</div>
        </div>
        <div class="video-metric-item">
            <div class="video-metric-label">Paid Video Views</div>
            <div class="video-metric-value">{{ number_format($analytics['videoMetrics']['page_video_views_paid'] ?? 0) }}</div>
        </div>
        <div class="video-metric-item">
            <div class="video-metric-label">Autoplayed Video Views</div>
            <div class="video-metric-value">{{ number_format($analytics['videoMetrics']['page_video_views_autoplayed'] ?? 0) }}</div>
        </div>
        <div class="video-metric-item">
            <div class="video-metric-label">Click-to-Play Video Views</div>
            <div class="video-metric-value">{{ number_format($analytics['videoMetrics']['page_video_views_click_to_play'] ?? 0) }}</div>
        </div>
        <div class="video-metric-item">
            <div class="video-metric-label">30s+ Complete Views (Organic)</div>
            <div class="video-metric-value">{{ number_format($analytics['videoMetrics']['page_video_complete_views_30s_organic'] ?? 0) }}</div>
        </div>
        <div class="video-metric-item">
            <div class="video-metric-label">30s+ Complete Views (Paid)</div>
            <div class="video-metric-value">{{ number_format($analytics['videoMetrics']['page_video_complete_views_30s_paid'] ?? 0) }}</div>
        </div>
    </div>

    <div class="page-footer">
        <div class="page-footer-grid">
            <div class="page-footer-cell">Facebook Analytics Report</div>
            <div class="page-footer-cell">05</div>
        </div>
    </div>
</div>
@endif

{{-- ========================================
     SUMMARY / THANK YOU
     ======================================== --}}
<div class="page">
    <h2 class="section-title">
        <span class="highlight">Summary</span>
    </h2>

    <div class="summary-box">
        <p style="margin-bottom: 15px;">
            This report provides comprehensive Facebook Page performance insights for the selected reporting period. 
            Key metrics include audience reach, engagement rates, content interactions, page views, and follower growth trends.
        </p>
        <p>
            The data presented enables strategic decision-making for content optimization, 
            audience targeting, and overall social media performance enhancement.
        </p>
    </div>

    <div style="margin-top: 80px; text-align: center; font-size: 10px; color: #666;">
        Generated on {{ now()->format('F d, Y \a\t H:i:s') }}
    </div>

    <div class="page-footer">
        <div class="page-footer-grid">
            <div class="page-footer-cell">Facebook Analytics Report</div>
            <div class="page-footer-cell">06</div>
        </div>
    </div>
</div>

</body>
</html>