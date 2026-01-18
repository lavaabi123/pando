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

    body {
        font-family: 'DejaVuSans', sans-serif;   
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding-left: 20px;
            padding-right: 20px;
            padding-bottom: 20px;
        }

        h1 {
            font-size: 16px;
            margin-bottom: 10px;
			color: #666;
			text-align:center;
        }

        .section {
            margin-bottom: 30px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 46px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
        }

        .table th {
            background-color: #f9f9f9;
        }


        .text-muted {
            color: #777;
        }
		/* Chart styling - 2 per row */
		/* Charts in 2 columns with proper spacing */
		.chart-container {
			display: inline-block;
			width: 45%;
			margin-bottom: 20px;
			margin-right: 0;
			padding: 10px;
			vertical-align: top;
			page-break-inside: avoid;
			box-sizing: border-box;
			border: 1px solid #e0e0e0;
		}

		.chart-container:nth-child(odd) {
			margin-right: 2%;
		}

		img.chart {
			display: block;
			width: 100%;
			height: auto;
			border: none;
			background: #fff;
		}
		.header {
            text-align: right;
            margin-bottom: 20px;
            font-size: 10px;
            color: #666;
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
            width: 33%;
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

    </style>
</head>
<body>
	{{-- Header --}}
    <div class="header">
		<div style="text-align:center">
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
				<img alt="Logo" src="{{ $base64im }}" style="width: 90px; height: 39px; margin-top: 15px;">
			@endif
			<h3 style="margin:0px">Brand: {{ $brand_name }}</h3>
			<h6 style="margin:5px 0px">Created on: {{ now()->format('F Y') }}</h6>
		</div>	
	</div>

    <h1>{{ __('Facebook Analytics Report') }}</h1>

    <div class="section" style="text-align:center;">
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
			@php
				$overview = $analytics['overview'];				
				// Chunk into groups of 3
				$metricsChunks = array_chunk($overview, 3, true);
			@endphp
			
			@foreach($metricsChunks as $chunk)
				<div class="metric-row">
					@foreach($chunk as $key => $item)
						<div class="metric-cell">
							<div class="metric-label">{{ __(ucwords(str_replace('_', ' ', $key))) }}</div>
							<div class="metric-value">{{ number_format($item['value']) }}</div>
						</div>
					@endforeach
					
					@for($i = count($chunk); $i < 3; $i++)
						<div class="metric-cell" style="border: none;"></div>
					@endfor
				</div>
			@endforeach
		</div>
    </div>

     <!-- Performance Trends -->
    @if (!empty($charts) && is_array($charts))
        <div class="section">
            <h3>{{ __('Performance Trends') }}</h3>
            @foreach ($charts as $chart)
                @if(isset($chart['base64']) && isset($chart['id']))
                    <div class="chart-container">
                        <img class="chart" src="{{ $chart['base64'] }}" alt="{{ $chart['title'] ?? 'Chart' }}" data-social="facebook">
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <!-- Daily Breakdown -->
    <!--<div class="section page-break">
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
    </div>-->

    <!-- Video Metrics (if available) -->
    @if (($analytics['metrics']['page_video_views_organic'] ?? 0) > 0 || ($analytics['metrics']['page_video_views_paid'] ?? 0) > 0)
    <!--<div class="section">
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
    </div>-->
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