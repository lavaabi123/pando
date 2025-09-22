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
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
        }

        .table th {
            background-color: #f9f9f9;
        }

        img.chart {
            max-width: 100%;
            margin-top: 10px;
            margin-bottom: 25px;
        }

        .text-muted {
            color: #777;
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
            <strong>{{ __('From') }}:</strong> {{ $startDate }}<br>
            <strong>{{ __('To') }}:</strong> {{ $endDate }}
        @endif
    </div>

    @if (!empty($charts) && is_array($charts))
        <div class="section">
            <h3>{{ __('Charts') }}</h3>
            @foreach ($charts as $chart)
                @if(isset($chart['base64']))
                    <img class="chart" src="{{ $chart['base64'] }}" alt="Chart">
                @endif
            @endforeach
        </div>
    @endif

    <div class="section">
        <h3>{{ __('Overview') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Metric') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Change') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($analytics['overview'] ?? [] as $key => $item)
                    <tr>
                        <td>{{ __(ucfirst(str_replace('_', ' ', $key))) }}</td>
                        <td>{{ number_format($item['value']) }}</td>
                        <td>{{ $item['change'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
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
    </div>

</body>
</html>
