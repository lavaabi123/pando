<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('LinkedIn Analytics Report') }}</title>
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
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        h3 {
            font-size: 14px;
            margin-bottom: 6px;
            margin-top: 24px;
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
    </style>
</head>
<body>

    <h1>{{ __('LinkedIn Analytics Report') }}</h1>

    <div class="section">
        <strong>{{ __('Page Name') }}:</strong> {{ $analytics['account']['name'] ?? '-' }}<br>
        <strong>{{ __('Username') }}:</strong> {{ $analytics['account']['username'] ?? '-' }}<br>
        <strong>{{ __('Profile URL') }}:</strong> {{ $analytics['account']['url'] ?? '-' }}<br>
        @if (!empty($startDate) && !empty($endDate))
            <br>
             <strong>{{ __('Period') }}:</strong> {{ $startDate }} -  {{ $endDate }}
        @endif
    </div>
	
    <div class="section">
        <h3>{{ __('Overview') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Metric') }}</th>
                    <th>{{ __('Total') }}</th>
                    <!--<th>{{ __('Change') }}</th>-->
                </tr>
            </thead>
            <tbody>
                @foreach ($analytics['overview'] ?? [] as $key => $item)
                    <tr>
                        <td>{{ __(ucfirst(str_replace('_', ' ', $key))) }}</td>
                        <td>{{ number_format($item['value']) }}</td>
                        <!--<td>{{ $item['change'] }}%</td>-->
                    </tr>
                @endforeach
            </tbody>
        </table>
		@if (!empty($charts) && is_array($charts))
				@foreach ($charts as $chart)
					@if(isset($chart['base64']))
						<div class="chart-container">
							<img class="chart" src="{{ $chart['base64'] }}" alt="{{ $chart['title'] ?? 'Chart' }}">
						</div>
					@endif
				@endforeach
		@endif
        </div>


    <!--<div class="section">
        <h3>{{ __('Top Countries') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Country') }}</th>
                    <th>{{ __('Fans') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($analytics['topFansCountries'] ?? [] as $row)
                    <tr>
                        <td>{{ $row['country'] }}</td>
                        <td>{{ number_format($row['fans']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>-->
	<div class="section">
        <h3>{{ __('Summary') }}</h3>
        <p>
            {{ __('This report shows the Linkedin performance for the selected period.') }}
            {{ __('The metrics include reach, likes, comments, shares, views and follower growth.') }}
        </p>
        <p style="margin-top: 20px; font-size: 10px; color: #666;">
            {{ __('Generated on') }}: {{ now()->format('Y-m-d H:i:s') }}
        </p>
    </div>

</body>
</html>
