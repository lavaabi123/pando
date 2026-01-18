<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Instagram Analytics Report') }}</title>
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

    <h1>{{ __('TikTok Analytics Report') }}</h1>

    <div class="section" style="text-align:center;">
        <strong>{{ __('Account Name') }}:</strong> <span class="highlight">{{ $analytics['account']['name'] ?? '-' }}</span><br>
        <strong>{{ __('Username') }}:</strong> <span class="highlight">{{ $analytics['account']['username'] ?? '-' }}</span><br>
        <strong>{{ __('Followers') }}:</strong> <span class="highlight">{{ number_format($analytics['account']['followers_count'] ?? 0) }}</span><br>
        <strong>{{ __('Posts') }}:</strong> <span class="highlight">{{ number_format($analytics['account']['media_count'] ?? 0) }}</span><br>
        @if (!empty($startDate) && !empty($endDate))
            <br>
            <strong>{{ __('From') }}:</strong> {{ $startDate }}<br>
            <strong>{{ __('To') }}:</strong> {{ $endDate }}
        @endif
    </div>

    @if (!empty($charts) && is_array($charts))
        <div class="section">
            <h3>{{ __('Charts') }}</h3>
            @foreach ($charts as $chart)
                @if(isset($chart['base64']))
					<div class="chart-container">
						<img class="chart" src="{{ $chart['base64'] }}" alt="{{ $chart['title'] ?? 'Chart' }}" data-social="tiktok">
					</div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="section">
        <h3>{{ __('Profile Overview') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Metric') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Change (%)') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $overview = $analytics['overview'] ?? [] @endphp
                <tr>
                    <td>{{ __('Likes') }}</td>
                    <td class="highlight">{{ number_format($overview['likes']['value'] ?? 0) }}</td>
                    <td>{{ ($overview['likes']['change'] ?? 0) . '%' }}</td>
                </tr>
                <tr>
                    <td>{{ __('Comments') }}</td>
                    <td class="highlight">{{ number_format($overview['comments']['value'] ?? 0) }}</td>
                    <td>{{ ($overview['comments']['change'] ?? 0) . '%' }}</td>
                </tr>
                <tr>
                    <td>{{ __('Shares') }}</td>
                    <td class="highlight">{{ number_format($overview['shares']['value'] ?? 0) }}</td>
                    <td>{{ ($overview['shares']['change'] ?? 0) . '%' }}</td>
                </tr>
                <tr>
                    <td>{{ __('Views') }}</td>
                    <td class="highlight">{{ number_format($overview['views']['value'] ?? 0) }}</td>
                    <td>{{ ($overview['views']['change'] ?? 0) . '%' }}</td>
                </tr>
                <tr>
                    <td>{{ __('Published Posts') }}</td>
                    <td class="highlight">{{ number_format($overview['published_videos']['value'] ?? 0) }}</td>
                    <td>{{ ($overview['published_videos']['change'] ?? 0) . '%' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

   
	<div class="section">
        <h3>{{ __('Summary') }}</h3>
        <p>
            {{ __('This report shows the TikTok performance for the selected period.') }}
            {{ __('The metrics include likes, comments, shares, views and follower growth.') }}
        </p>
        <p style="margin-top: 20px; font-size: 10px; color: #666;">
            {{ __('Generated on') }}: {{ now()->format('Y-m-d H:i:s') }}
        </p>
    </div>

</body>
</html>
