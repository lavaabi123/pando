<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Instagram Analytics Report') }}</title>
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
            border-bottom: 2px solid #e1306c;
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
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px 10px;
            text-align: left;
        }
        .table th {
            background-color: #f0f0f0;
        }
        .table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        .highlight {
            font-weight: bold;
            color: #e1306c;
        }
        .subtext {
            color: #888;
            font-size: 11px;
            margin-top: 3px;
        }
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
    </style>
</head>
<body>

    <h1>{{ __('TikTok Analytics Report') }}</h1>

    <div class="section">
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
						<img class="chart" src="{{ $chart['base64'] }}" alt="Chart">
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
