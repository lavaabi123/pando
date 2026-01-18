<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Consolidated Social Media Analytics Report') }}</title>
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
            font-size: 11px;
            line-height: 1.4;
            color: #222;
            padding: 15px;
    }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .header {
            text-align: right;
            margin-bottom: 20px;
            font-size: 10px;
            color: #666;
        }

        .logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }

        .accounts-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .account-row {
            display: table-row;
        }

        .account-cell {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
            border: 1px solid #e0e0e0;
        }

        .account-header {
            padding: 8px 12px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: bold;
        }

        .platform-icon {
            display: inline-block;
            margin-right: 5px;
        }

        .chart-image {
            width: 100%;
            height: auto;
            margin-bottom: 10px;
            border: 1px solid #e0e0e0;
        }

        .metrics-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .metric-row {
            display: table-row;
        }

        .metric-cell {
            display: table-cell;
            width: 33.33%;
            padding: 8px;
            text-align: center;
            border: 1px solid #e0e0e0;
            background: #f8f9fa;
            vertical-align: middle;
        }

        .metric-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }

        .metric-value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin: 3px 0;
        }

        .metric-change {
            font-size: 10px;
            font-weight: 600;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
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
	
<!--
    <h1>{{ __('Social Media Analytics Overview') }}</h1>
    
    @if (!empty($startDate) && !empty($endDate))
        <p style="margin-bottom: 15px; color: #666;">
            {{ __('Period') }}: {{ $startDate }} - {{ $endDate }}
        </p>
    @endif
-->

    {{-- Accounts Grid - 2x2 Layout --}}
    @if(!empty($charts) && is_array($charts))
        @php
            $accountsData = [];
            
            // Organize data by account
            foreach($charts as $accountId => $accountCharts) {
                if (!empty($accountCharts) && is_array($accountCharts)) {
                    $firstChart = $accountCharts[0] ?? null;
                    if ($firstChart) {
                        // Try to find matching analytics data
                        $matchedAnalytics = null;
                        
                        if (!empty($allAnalytics)) {
                            foreach ($allAnalytics as $analyticsData) {
                                // Match by account ID secure or by social network
                                $analyticsAccountId = $analyticsData['account']->id_secure ?? null;
                                $analyticsSocial = $analyticsData['social'] ?? null;
                                $chartSocial = $firstChart['social'] ?? null;
                                
                                
                                // Match by account ID first
                                if ($analyticsAccountId === $accountId) {
                                    $matchedAnalytics = $analyticsData;
                                    break;
                                }
                                
                                // Fallback: match by social network
                                if ($analyticsSocial === $chartSocial && !$matchedAnalytics) {
                                    $matchedAnalytics = $analyticsData;
                                }
                            }
                        }
                        
                        $accountsData[] = [
                            'id' => $accountId,
                            'social' => $firstChart['social'] ?? 'unknown',
                            'name' => $firstChart['accountName'] ?? 'unknown',
                            'title' => $firstChart['title'] ?? ucfirst($firstChart['social'] ?? 'Account'),
                            'charts' => $accountCharts,
                            'analytics' => $matchedAnalytics
                        ];
                    }
                }
            }
            
            // Group accounts into rows of 2
            $accountRows = array_chunk($accountsData, 2);
        @endphp

        <div class="accounts-grid">
            @foreach($accountRows as $rowIndex => $row)
                <div class="account-row">
                    @foreach($row as $account)
                        <div class="account-cell">
                            {{-- Platform Header --}}
                            <div class="account-header" style="padding: 12px 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 10px;">
    @php
        $iconPath = public_path('img/social/' . $account['social'] . '.png');
        $base64Icon = null;
        
        if (file_exists($iconPath)) {
            $type = pathinfo($iconPath, PATHINFO_EXTENSION);
            $data = file_get_contents($iconPath);
            $base64Icon = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    @endphp
    
    @if($base64Icon)
        <img alt="{{ ucfirst($account['social']) }}" 
             src="{{ $base64Icon }}" 
             style="width: 17px; height: 17px; vertical-align: middle; margin-right: 5px;">
    @endif
    
    <span style="font-size: 13px; font-weight: bold; vertical-align: middle;">
        {{ ucfirst($account['social']) }} ( {{ $account['name'] ?? $account['name'] ?? 'Account' }} )
    </span>
</div>

                            {{-- Main Chart --}}
                            @if(!empty($account['charts'][0]['base64']))
                                <img src="{{ $account['charts'][0]['base64'] }}" 
                                     alt="{{ $account['title'] }}" 
                                     class="chart-image">
                            @endif

                            {{-- Metrics --}}
@php
    $metrics = [];
    
    if (!empty($account['analytics'])) {
        $overview = $account['analytics']['analytics']['overview'] ?? [];
        
        \Log::info('Processing metrics for ' . $account['social'], [
            'overview_keys' => array_keys($overview)
        ]);
        
        // Convert to indexed array
        $metricsArray = [];
        foreach ($overview as $metricKey => $metricData) {
            $metricsArray[] = [
                'key' => $metricKey,
                'label' => $metricData['label'] ?? __(ucwords(str_replace('_', ' ', $metricKey))),
                'value' => $metricData['value'] ?? 0,
                'change' => $metricData['change'] ?? null
            ];
        }
        $metrics = array_slice($metricsArray, 0, 6);
        
    }
@endphp

@if(!empty($metrics))
    <div class="metrics-grid">
        @foreach(array_chunk($metrics, 3) as $rowIndex => $metricRow)
            <div class="metric-row">
                @foreach($metricRow as $metric)
                    <div class="metric-cell">
                        <div class="metric-label">
                            {{ $metric['label'] }}
                        </div>
                        <div class="metric-value">
                            {{ number_format($metric['value']) }}
                        </div>
                        @if($metric['change'] !== null)
                            @php
                                $change = $metric['change'];
                                $absChange = abs($change);
                                if ($absChange >= 100) {
                                    $display = '100%';
                                } else {
                                    $display = number_format($absChange, 0) . '%';
                                }
                            @endphp
                            <div class="metric-change {{ $change >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $change >= 0 ? '+' : '-' }} {{ $display }}
                            </div>
                        @endif
                    </div>
                @endforeach
                
                {{-- Fill empty cells if less than 3 --}}
                @for($i = count($metricRow); $i < 3; $i++)
                    <div class="metric-cell" style="visibility: hidden; border: 1px solid #e0e0e0; background: #f8f9fa;"></div>
                @endfor
            </div>
        @endforeach
    </div>
@else
    <div style="text-align: center; padding: 10px; color: #999; font-size: 10px;">
        No metrics available
    </div>
@endif
                        </div>
                    @endforeach
                    
                    {{-- Fill empty cell if odd number of accounts --}}
                    @if(count($row) === 1)
                        <div class="account-cell" style="border: none;"></div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 50px;">
            <p>{{ __('No data available') }}</p>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        {{ __('Generated by Pando - Social Media Management Platform') }}<br>
        {{ __('Report generated on') }}: {{ now()->format('Y-m-d H:i:s') }}
    </div>

</body>
</html>