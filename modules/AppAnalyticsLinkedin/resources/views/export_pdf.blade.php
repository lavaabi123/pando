<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('LinkedIn Analytics Report') }}</title>
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

    <h1>{{ __('LinkedIn Analytics Report') }}</h1>

    <div class="section" style="text-align:center;">
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
							<img class="chart" src="{{ $chart['base64'] }}" alt="{{ $chart['title'] ?? 'Chart' }}" data-social="linkedin">
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
