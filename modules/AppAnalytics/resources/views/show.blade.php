{{-- Modules/AppAnalyticsLinkedin/Resources/views/show.blade.php --}}
@extends('layouts.app') {{-- adjust to your layout --}}

@section('content')

<div style="padding:24px;">

    {{-- ── TOP BAR ────────────────────────────────────────────────── --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">
                LinkedIn Analytics
            </div>
            <h4 style="margin:0; font-size:22px; font-weight:400;">Rimberio Marketing</h4>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <input type="text" id="daterange-picker" name="daterange"
                   class="form-control form-control-sm"
                   value="2026-01-01,2026-01-31"
                   style="width:200px;" />
            <button id="btn-export-pdf"
                    class="btn btn-sm"
                    style="background:#b7ff00; color:#000; font-weight:700; padding:6px 18px; border:none; border-radius:4px; cursor:pointer;">
                &#8595; Export PDF
            </button>
        </div>
    </div>

    {{-- ── KPI CARDS ────────────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:24px;">
        @php
        $kpis = [
            ['label'=>'Impressions',    'value'=>'12,260', 'change'=>'+45.2%'],
            ['label'=>'Engagement Rate','value'=>'8.6%',   'change'=>null],
            ['label'=>'Clicks',         'value'=>'596',    'change'=>null],
            ['label'=>'Reach/Members',  'value'=>'6,272',  'change'=>null],
            ['label'=>'Page Views',     'value'=>'800',    'change'=>null],
        ];
        @endphp
        @foreach($kpis as $kpi)
        <div style="background:#111; border:1px solid #1e1e1e; border-radius:8px; padding:16px;">
            <div style="font-size:10px; color:#666; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">
                {{ $kpi['label'] }}
            </div>
            <div style="font-size:24px; font-weight:300; color:#fff;">{{ $kpi['value'] }}</div>
            @if($kpi['change'])
                <div style="font-size:11px; color:#b7ff00; margin-top:4px;">{{ $kpi['change'] }}</div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ── MAIN TREND CHART ─────────────────────────────────────────── --}}
    <div style="background:#111; border:1px solid #1e1e1e; border-radius:8px; padding:20px; margin-bottom:24px;">
        <div style="font-size:10px; color:#b7ff00; text-transform:uppercase; letter-spacing:1px; margin-bottom:14px;">
            Performance Trend (May – Aug)
        </div>
        <canvas id="chart-main" height="90"></canvas>
    </div>

    {{-- ── MINI CHARTS 2×2 ─────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:24px;">
        @php
        $stats = [
            ['id'=>'chart-comments',  'label'=>'Comments',       'val'=>'68',  'chg'=>'+466'],
            ['id'=>'chart-reactions', 'label'=>'Reactions',      'val'=>'385', 'chg'=>'+52.2%'],
            ['id'=>'chart-visitors',  'label'=>'Unique Visitors','val'=>'356', 'chg'=>'+51.5%'],
            ['id'=>'chart-followers', 'label'=>'New Followers',  'val'=>'42',  'chg'=>'+44.8%'],
        ];
        @endphp
        @foreach($stats as $s)
        <div style="background:#111; border:1px solid #1e1e1e; border-radius:8px; padding:16px;">
            <div style="font-size:10px; color:#666; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">
                {{ $s['label'] }}
            </div>
            <div style="font-size:26px; font-weight:300; color:#fff; margin-bottom:2px;">{{ $s['val'] }}</div>
            <div style="font-size:10px; color:#b7ff00; margin-bottom:12px;">{{ $s['chg'] }}</div>
            <canvas id="{{ $s['id'] }}" height="70"></canvas>
        </div>
        @endforeach
    </div>

    {{-- ── AUDIENCE + ROI ROW ───────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:12px; margin-bottom:24px;">

        {{-- Industries donut --}}
        <div style="background:#111; border:1px solid #1e1e1e; border-radius:8px; padding:20px;">
            <div style="font-size:10px; color:#b7ff00; text-transform:uppercase; letter-spacing:1px; margin-bottom:14px;">
                Top Industries
            </div>
            <div style="display:flex; align-items:center; gap:30px;">
                <div style="flex:0 0 200px;">
                    <canvas id="chart-industries"></canvas>
                </div>
                <div style="flex:1;">
                    @php
                    $industries = [
                        ['name'=>'Financial Services',          'pct'=>40, 'color'=>'#a8c93a'],
                        ['name'=>'Gambling Facilities & Casinos','pct'=>30, 'color'=>'#c8ff00'],
                        ['name'=>'IT Services',                 'pct'=>15, 'color'=>'#8fa347'],
                        ['name'=>'Technology & Information',    'pct'=>10, 'color'=>'#6f7a50'],
                        ['name'=>'Software Development',        'pct'=>10, 'color'=>'#5c6548'],
                        ['name'=>'Hospitality',                 'pct'=>10, 'color'=>'#7c845a'],
                    ];
                    @endphp
                    @foreach($industries as $ind)
                    <div style="display:flex; align-items:center; margin-bottom:8px; border-bottom:1px solid #1a1a1a; padding-bottom:8px;">
                        <span style="width:13px;height:13px;background:{{ $ind['color'] }};display:inline-block;border-radius:2px;margin-right:10px;flex-shrink:0;"></span>
                        <span style="flex:1; font-size:12px; color:#ccc;">{{ $ind['name'] }}</span>
                        <span style="color:#b7ff00; margin:0 12px; font-size:14px;">&#8594;</span>
                        <span style="font-size:13px; font-weight:700;">{{ $ind['pct'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ROI Bar --}}
        <div style="background:#111; border:1px solid #1e1e1e; border-radius:8px; padding:20px;">
            <div style="font-size:10px; color:#b7ff00; text-transform:uppercase; letter-spacing:1px; margin-bottom:14px;">
                ROI Analysis
            </div>
            <canvas id="chart-roi" height="160"></canvas>
        </div>
    </div>

    {{-- ── TOP POSTS ROW ────────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px;">
        @php
        $posts = [
            ['id'=>'chart-post-a','key'=>'post_a','name'=>'Post A','likes'=>3000,'comments'=>1200,'shares'=>1500,'type'=>'Images','rate'=>5.0],
            ['id'=>'chart-post-b','key'=>'post_b','name'=>'Post B','likes'=>950, 'comments'=>250, 'shares'=>100, 'type'=>'Videos','rate'=>4.0],
        ];
        @endphp
        @foreach($posts as $p)
        <div style="background:#111; border:1px solid #1e1e1e; border-radius:8px; padding:20px;">
            <div style="font-size:10px; color:#b7ff00; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">Top Post</div>
            <div style="font-size:26px; font-weight:300; margin-bottom:8px;">{{ $p['name'] }}</div>
            <div style="font-size:11px; color:#ccc; margin-bottom:12px;">
                <span style="color:#b7ff00;">&#9829;</span> {{ number_format($p['likes']) }} &nbsp;
                <span style="color:#b7ff00;">&#9679;</span> {{ number_format($p['comments']) }} &nbsp;
                <span style="color:#b7ff00;">&#10148;</span> {{ number_format($p['shares']) }}
            </div>
            <canvas id="{{ $p['id'] }}" height="70" style="margin-bottom:12px;"></canvas>
            <div style="font-size:10px; color:#b7ff00; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Content Types Analysis</div>
            <div style="font-size:13px; color:#ccc; margin-bottom:4px;">{{ $p['type'] }}</div>
            <div style="font-size:40px; font-weight:300; color:#fff; line-height:1;">{{ number_format($p['rate'],1) }}%</div>
            <div style="font-size:10px; color:#666; margin-top:2px;">Engagement Rate</div>
        </div>
        @endforeach
    </div>

    {{-- ── SENTIMENT ────────────────────────────────────────────────── --}}
    <div style="background:#111; border:1px solid #1e1e1e; border-radius:8px; padding:20px; margin-bottom:24px;">
        <div style="font-size:10px; color:#b7ff00; text-transform:uppercase; letter-spacing:1px; margin-bottom:16px;">
            Sentiment Analysis
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px;">
            <div>
                <div style="font-size:11px; color:#888; margin-bottom:16px;">Sentiment Overview</div>
                @foreach([['Positive','70%'],['Neutral','20%'],['Negative','10%']] as $s)
                <div style="display:flex; align-items:center; margin-bottom:18px;">
                    <span style="color:#bfc6d2; font-size:13px; width:160px;">{{ $s[0] }} sentiment</span>
                    <span style="color:#b7ff00; font-size:18px; margin:0 16px;">&#8594;</span>
                    <span style="font-size:36px; font-weight:300;">{{ $s[1] }}</span>
                </div>
                @endforeach
            </div>
            <div style="border-left:1px solid #1e1e1e; padding-left:30px;">
                <div style="font-size:11px; color:#888; margin-bottom:16px;">Key Sentiments</div>
                @foreach([
                    ['Positive Comment','"Great content!"'],
                    ['Neutral Comment','"Interesting Post"'],
                    ['Negative Sentiment','"Could be better"'],
                ] as $c)
                <div style="margin-bottom:16px;">
                    <div style="font-size:9px; color:#666; text-transform:uppercase; letter-spacing:1px; margin-bottom:3px;">{{ $c[0] }}</div>
                    <div style="font-size:15px; font-style:italic;">{{ $c[1] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div><!-- end padding wrapper -->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ─── Global Chart.js defaults ─────────────────────── */
    Chart.defaults.color          = '#9f9f9f';
    Chart.defaults.borderColor    = 'rgba(255,255,255,0.08)';

    const GREEN   = '#b7ff00';
    const GRID    = 'rgba(255,255,255,0.08)';
    const MONTHS  = ['May','June','July','August'];

    /* ─── Shared scale configs ──────────────────────────── */
    const scaleBase = {
        x: { grid:{display:false}, ticks:{color:'#cfcfcf', font:{size:9}} },
        y: { grid:{color:GRID},    ticks:{color:'#9f9f9f', font:{size:9}} },
    };

    /* ─── Helper: line dataset ──────────────────────────── */
    function lineDS(data, color) {
        return {
            data, borderColor:color, backgroundColor:'transparent',
            pointBackgroundColor:color, pointRadius:4,
            borderWidth:2.5, tension:0.4,
        };
    }

    /* ═══ CHART 1: Main Trend ═══════════════════════════════ */
    const chartMain = new Chart(document.getElementById('chart-main'), {
        type:'line',
        data:{
            labels: MONTHS,
            datasets:[
                { label:'Unique Visitors', ...lineDS([100000,120000,85000,135000], GREEN) },
                { label:'Impressions',     ...lineDS([4000,8000,7800,5800],       '#00d4ff') },
                { label:'Reactions',       ...lineDS([4500,7500,7800,5800],       '#ff9900') },
            ]
        },
        options:{
            responsive:true,
            plugins:{ legend:{ labels:{ color:'#ccc', font:{size:10}, boxWidth:10 } } },
            scales: scaleBase,
        }
    });

    /* ═══ CHART 2–5: Mini Line Charts ═══════════════════════ */
    const miniData = {
        'chart-comments' : [4000,8000,7800,5800],
        'chart-reactions': [4500,7500,7800,5800],
        'chart-visitors' : [100000,120000,85000,135000],
        'chart-followers': [30,40,35,42],
    };

    const miniCharts = {};
    for (const [id, data] of Object.entries(miniData)) {
        miniCharts[id] = new Chart(document.getElementById(id), {
            type:'line',
            data:{ labels:MONTHS, datasets:[{ ...lineDS(data, GREEN), label:'' }] },
            options:{
                responsive:true,
                plugins:{ legend:{display:false} },
                scales: scaleBase,
            }
        });
    }

    /* ═══ CHART 6: Industries Donut ═════════════════════════ */
    const chartIndustries = new Chart(document.getElementById('chart-industries'), {
        type:'doughnut',
        data:{
            labels:['Financial','Gambling','IT','Technology','Software','Hospitality'],
            datasets:[{
                data:[40,30,15,10,10,10],
                backgroundColor:['#a8c93a','#c8ff00','#8fa347','#6f7a50','#5c6548','#7c845a'],
                borderWidth:0,
            }]
        },
        options:{ cutout:'56%', plugins:{ legend:{display:false} } }
    });

    /* ═══ CHART 7: ROI Bar ══════════════════════════════════ */
    const chartRoi = new Chart(document.getElementById('chart-roi'), {
        type:'bar',
        data:{
            labels:['Campaign X','Campaign Y'],
            datasets:[{
                data:[4,5],
                backgroundColor: GREEN,
                borderRadius:6,
            }]
        },
        options:{
            plugins:{ legend:{display:false} },
            scales:{
                x:{ grid:{display:false}, ticks:{color:'#cfcfcf'} },
                y:{ grid:{color:GRID},    ticks:{color:'#9f9f9f'}, min:0, max:6 },
            }
        }
    });

    /* ═══ CHART 8–9: Top Posts Horizontal Bar ═══════════════ */
    function postBar(canvasId, data) {
        return new Chart(document.getElementById(canvasId), {
            type:'bar',
            data:{
                labels:['Like','Comment','Share'],
                datasets:[{
                    data, backgroundColor:GREEN, borderRadius:3, barThickness:14,
                }]
            },
            options:{
                indexAxis:'y',
                plugins:{ legend:{display:false} },
                scales:{
                    x:{ grid:{color:GRID}, ticks:{color:'#a8a8a8', font:{size:9}} },
                    y:{ grid:{display:false}, ticks:{color:'#cfcfcf', font:{size:9}} },
                }
            }
        });
    }

    const chartPostA = postBar('chart-post-a', [3000,1200,1500]);
    const chartPostB = postBar('chart-post-b', [950, 250,  100]);

    /* ════════════════════════════════════════════════════════
       PDF EXPORT — capture charts as base64, POST to Laravel
    ════════════════════════════════════════════════════════ */
    document.getElementById('btn-export-pdf').addEventListener('click', function () {

        const btn = this;
        btn.textContent = 'Generating…';
        btn.disabled = true;

        // Give Chart.js a tick to fully render before capture
        requestAnimationFrame(() => {
            const charts = {
                main       : chartMain.toBase64Image('image/png',1),
                comments   : miniCharts['chart-comments'].toBase64Image('image/png',1),
                reactions  : miniCharts['chart-reactions'].toBase64Image('image/png',1),
                visitors   : miniCharts['chart-visitors'].toBase64Image('image/png',1),
                followers  : miniCharts['chart-followers'].toBase64Image('image/png',1),
                industries : chartIndustries.toBase64Image('image/png',1),
                roi        : chartRoi.toBase64Image('image/png',1),
                post_a     : chartPostA.toBase64Image('image/png',1),
                post_b     : chartPostB.toBase64Image('image/png',1),
            };

            // Build hidden form and submit (triggers browser download)
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("analytics.exportnew.pdf") }}';
            form.style.display = 'none';

            const fields = {
                _token    : '{{ csrf_token() }}',
                charts    : JSON.stringify(charts),
                daterange : document.getElementById('daterange-picker').value,
            };

            for (const [name, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = name;
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

            setTimeout(() => {
                btn.textContent = '↓ Export PDF';
                btn.disabled    = false;
            }, 2000);
        });
    });

});
</script>
@endpush