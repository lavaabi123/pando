<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Monthly Performance Report</title>
<style>
/*
|------------------------------------------------------------------------
| BLANK PAGE FIX — The definitive DomPDF landscape approach:
|
| Each slide is a <table> with height exactly equal to one page.
| DomPDF honours table heights and does NOT add overflow pages
| when the table height matches the @page size.
|
| A4 landscape inner area = 297mm × 210mm
| With 0 margin @page, the usable area is the full 297×210mm.
| We use 275mm × 195mm per table (leaving breathing room).
|------------------------------------------------------------------------
*/

@page {
    size: a4 landscape;
    margin: 0;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html, body {
    background: #000;
    color: #fff;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    width: 297mm;
}

/*
 * Each slide = one full-page table.
 * height:210mm + page-break-after:always = exactly one page, no overflow.
 */
.page {
    width: 297mm;
    height: 210mm;
    page-break-after: always;
    background: #000;
    display: block;
    overflow: hidden;
    padding: 20px 28px 18px 28px;
    position: relative;
}
.page:last-child {
    page-break-after: avoid;
}

/* ── Typography ──────────────────────────────────────────── */
.lime   { color: #b7ff00; }
.italic { font-style: italic; }

.sh {
    font-size: 30px;
    font-weight: 300;
    line-height: 1.0;
    margin-bottom: 2px;
}

.div {
    border: none;
    border-top: 1px solid #252525;
    margin: 5px 0 9px;
    display: block;
}

.slbl {
    color: #b7ff00;
    font-size: 9px;
    margin-bottom: 5px;
    display: block;
}

/* ── Footer ──────────────────────────────────────────────── */
.ftr {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    border-top: 1px solid #1a1a1a;
    padding: 3px 28px;
    background: #000;
}
.ftr table { width: 100%; border-collapse: collapse; }
.ftr td { font-size: 7px; color: #444; padding: 0; }
.ftr .fc { text-align: center; }
.ftr .fr { text-align: right; }

/* ── Layout ──────────────────────────────────────────────── */
table.L { width: 100%; border-collapse: collapse; }
table.L td { vertical-align: top; padding: 0; }

/* ── KPI Cards ───────────────────────────────────────────── */
.kpi   { background: #0d0d0d; border: 1px solid #1e1e1e; padding: 9px 10px 7px; }
.kpi-l { font-size: 7px; color: #555; text-transform: uppercase; letter-spacing: 0.8px; }
.kpi-v { font-size: 20px; font-weight: 300; margin-top: 3px; }
.kpi-d { font-size: 9px; color: #b7ff00; margin-top: 2px; }

/* ── Stat (Performance) ──────────────────────────────────── */
.sl { font-size: 8px; color: #bfc6d2; }
.sv { font-size: 25px; font-weight: 300; line-height: 1.1; }
.sd { font-size: 8px; color: #b7ff00; }

/* ── Cover ───────────────────────────────────────────────── */
.ct  { font-size: 48px; font-weight: 300; line-height: 1.0; margin: 10px 0 14px; }
.chi { color: #b7ff00; }

/* ── TOC ─────────────────────────────────────────────────── */
.tn { font-size: 20px; font-weight: 300; color: #333; }
.tl { font-size: 11px; color: #ccc; }

/* ── Audience ────────────────────────────────────────────── */
.ln { font-size: 11px; color: #ddd; }
.lp { font-size: 12px; font-weight: bold; }

/* ── Content ─────────────────────────────────────────────── */
.pn { font-size: 19px; font-weight: 300; margin-bottom: 3px; }
.eb { font-size: 28px; font-weight: 300; }
.el { font-size: 8px; color: #bfc6d2; }

/* ── Campaign ────────────────────────────────────────────── */
.cn { font-size: 14px; font-weight: bold; margin-bottom: 6px; }
.cl { font-size: 8px; color: #bfc6d2; margin-top: 4px; }
.cv { font-size: 17px; font-weight: 300; }

/* ── Sentiment ───────────────────────────────────────────── */
.ssl { font-size: 11px; color: #bfc6d2; }
.ssp { font-size: 34px; font-weight: 300; }
.ct2 { font-size: 7px; color: #bfc6d2; }
.ctx { font-size: 12px; }

/* ── Contact ─────────────────────────────────────────────── */
.col { font-size: 7px; color: #b7ff00; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 3px; }
.cov { font-size: 11px; line-height: 1.5; }
</style>
</head>
<body>

{{-- ════════════════════════════════════════════════════════
     SLIDE 1 — Cover
════════════════════════════════════════════════════════════ --}}
<div class="page">
    <div style="font-size:8px;color:#444;margin-bottom:4px;">{{ $company }}</div>
    <div class="ct">
        Monthly<br>
        <span class="italic">P</span>erformance<br>
        <span class="chi">Report</span>
    </div>
    <div style="font-size:10px;color:#777;">
        Presented by &nbsp;<strong style="color:#fff;">{{ $presenter }}</strong>
    </div>
    <div style="font-size:9px;color:#444;margin-top:3px;">{{ $website }}</div>
    <div style="font-size:20px;font-weight:700;letter-spacing:3px;margin-top:12px;">
        {{ strtoupper($month) }}
    </div>

    <div class="ftr">
        <table><tr>
            <td>{{ $company }}</td>
            <td class="fc">Monthly Performance Report</td>
            <td class="fr">{{ $website }}</td>
        </tr></table>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     SLIDE 2 — Table of Contents
════════════════════════════════════════════════════════════ --}}
<div class="page">
    <div style="font-size:22px;font-weight:300;color:#b7ff00;margin-bottom:6px;">Table of Contents</div>
    <hr class="div">
    <table class="L" style="margin-top:5px;">
        <tr>
            <td style="width:50%;padding:5px 0;"><span class="tn">03</span>&nbsp;&nbsp;<span class="tl">LinkedIn Analytics</span></td>
            <td style="width:50%;padding:5px 0;"><span class="tn">04</span>&nbsp;&nbsp;<span class="tl">LinkedIn Performance</span></td>
        </tr>
        <tr>
            <td style="padding:5px 0;"><span class="tn">05</span>&nbsp;&nbsp;<span class="tl">Audience Insights</span></td>
            <td style="padding:5px 0;"><span class="tn">06</span>&nbsp;&nbsp;<span class="tl">Content Performance</span></td>
        </tr>
        <tr>
            <td style="padding:5px 0;"><span class="tn">07</span>&nbsp;&nbsp;<span class="tl">Campaign Analysis</span></td>
            <td style="padding:5px 0;"><span class="tn">08</span>&nbsp;&nbsp;<span class="tl">Sentiment Analysis</span></td>
        </tr>
        <tr>
            <td style="padding:5px 0;"><span class="tn">09</span>&nbsp;&nbsp;<span class="tl">Contact Information</span></td>
            <td></td>
        </tr>
    </table>
    <div class="ftr"><table><tr>
        <td>{{ $company }}</td><td class="fc">Monthly Performance Report</td><td class="fr">{{ $website }}</td>
    </tr></table></div>
</div>

{{-- ════════════════════════════════════════════════════════
     SLIDE 3 — LinkedIn Analytics
════════════════════════════════════════════════════════════ --}}
<div class="page">
    <div class="sh"><span class="italic">L</span>inkedIn <span class="italic">A</span>nalytics</div>
    <p style="font-size:9px;color:#666;margin:4px 0 10px;line-height:1.5;max-width:460px;">
        January delivered strong organic growth and engagement on LinkedIn for Passport Technology.
        Performance improved across impressions, engagement signals, and follower growth.
    </p>
    <hr class="div">

    {{-- 5 KPI cards ─────────────────────────────────── --}}
    <table style="width:100%;border-collapse:separate;border-spacing:0 0;">
        <tr>
            <td class="kpi" style="width:19%;">
                <div class="kpi-l">Impressions</div>
                <div class="kpi-v">{{ $analytics['impressions'] }}</div>
                <div class="kpi-d">{{ $analytics['impressions_delta'] }}</div>
            </td>
            <td style="width:6px;"></td>
            <td class="kpi" style="width:19%;">
                <div class="kpi-l">Engagement Rate</div>
                <div class="kpi-v">{{ $analytics['engagement_rate'] }}</div>
                <div class="kpi-d">&nbsp;</div>
            </td>
            <td style="width:6px;"></td>
            <td class="kpi" style="width:19%;">
                <div class="kpi-l">Clicks</div>
                <div class="kpi-v">{{ $analytics['clicks'] }}</div>
                <div class="kpi-d">&nbsp;</div>
            </td>
            <td style="width:6px;"></td>
            <td class="kpi" style="width:19%;">
                <div class="kpi-l">Members Reached</div>
                <div class="kpi-v">{{ $analytics['members'] }}</div>
                <div class="kpi-d">&nbsp;</div>
            </td>
            <td style="width:6px;"></td>
            <td class="kpi" style="width:19%;">
                <div class="kpi-l">Page Views</div>
                <div class="kpi-v">{{ $analytics['page_views'] }}</div>
                <div class="kpi-d">&nbsp;</div>
            </td>
        </tr>
    </table>

    <div class="ftr"><table><tr>
        <td>{{ $company }}</td><td class="fc">Monthly Performance Report</td><td class="fr">{{ $website }}</td>
    </tr></table></div>
</div>

{{-- ════════════════════════════════════════════════════════
     SLIDE 4 — LinkedIn Performance
════════════════════════════════════════════════════════════ --}}
<div class="page">
    <div class="sh">LinkedIn <span class="italic">P</span>erformance</div>
    <hr class="div">

    <table class="L">
        <tr>
            <td style="width:25%;border-right:1px solid #1e1e1e;padding-right:14px;vertical-align:top;">
                <div class="sl">Unique Visitors</div>
                <div class="sv">{{ $performance['unique_visitors'] }}</div>
                <div class="sd">{{ $performance['unique_visitors_delta'] }}</div>
                <div style="margin-top:8px;">{!! $charts['trend'] !!}</div>
            </td>
            <td style="width:25%;padding:0 14px;vertical-align:top;">
                <div class="sl">Comments</div>
                <div class="sv">{{ $performance['comments'] }}</div>
                <div class="sd">({{ $performance['comments_delta'] }})</div>
                <div style="margin-top:8px;">{!! $charts['trend'] !!}</div>
            </td>
            <td style="width:25%;padding:0 14px;border-left:1px solid #1a1a1a;vertical-align:top;">
                <div class="sl">Reactions</div>
                <div class="sv">{{ $performance['reactions'] }}</div>
                <div class="sd">({{ $performance['reactions_delta'] }})</div>
                <div style="margin-top:8px;">{!! $charts['trend'] !!}</div>
            </td>
            <td style="width:25%;padding-left:14px;border-left:1px solid #1a1a1a;vertical-align:top;">
                <div class="sl">New Followers</div>
                <div class="sv">{{ $performance['new_followers'] }}</div>
                <div class="sd">({{ $performance['new_followers_delta'] }})</div>
                <div style="margin-top:8px;">{!! $charts['trend'] !!}</div>
            </td>
        </tr>
    </table>

    <div class="ftr"><table><tr>
        <td>{{ $company }}</td><td class="fc">Monthly Performance Report</td><td class="fr">{{ $website }}</td>
    </tr></table></div>
</div>

{{-- ════════════════════════════════════════════════════════
     SLIDE 5 — LinkedIn Audience
════════════════════════════════════════════════════════════ --}}
<div class="page">
    <div style="font-size:8px;color:#b7ff00;margin-bottom:4px;">{{ $company }}</div>
    <div class="sh">LinkedIn <span class="italic">A</span>udience</div>
    <hr class="div">
    <span class="slbl">Top Industries</span>

    <table class="L">
        <tr>
            <td style="width:155px;padding-right:22px;vertical-align:middle;">
                {!! $charts['donut'] !!}
            </td>
            <td style="vertical-align:top;">
                @foreach($audience['industries'] as $ind)
                <table style="width:340px;border-collapse:collapse;margin-bottom:8px;">
                    <tr>
                        <td style="width:13px;vertical-align:middle;padding-right:7px;">
                            <div style="width:11px;height:11px;background:{{ $ind['color'] }};"></div>
                        </td>
                        <td class="ln" style="vertical-align:middle;">{{ $ind['name'] }}</td>
                        <td style="width:24px;color:#b7ff00;text-align:center;font-size:12px;font-weight:bold;vertical-align:middle;">-&gt;</td>
                        <td style="width:36px;text-align:right;vertical-align:middle;" class="lp">{{ $ind['value'] }}%</td>
                    </tr>
                </table>
                @endforeach
            </td>
        </tr>
    </table>

    <div class="ftr"><table><tr>
        <td>{{ $company }}</td><td class="fc">Monthly Performance Report</td><td class="fr">{{ $website }}</td>
    </tr></table></div>
</div>

{{-- ════════════════════════════════════════════════════════
     SLIDE 6 — Content Performance
════════════════════════════════════════════════════════════ --}}
<div class="page">
    <div style="font-size:8px;color:#b7ff00;margin-bottom:4px;">{{ $company }}</div>
    <div class="sh"><span class="italic">C</span>ontent <span class="italic">P</span>erformance</div>
    <hr class="div">

    <table class="L">
        <tr>
            {{-- Post A --}}
            <td style="width:49%;padding-right:16px;border-right:1px solid #1e1e1e;vertical-align:top;">
                <span class="slbl">Top Posts:</span>
                <div class="pn">Post A</div>
                <table style="width:100%;border-collapse:collapse;margin-bottom:7px;font-size:9px;color:#bfc6d2;">
                    <tr>
                        <td style="padding-right:12px;"><span style="color:#b7ff00;">Likes</span>&nbsp;{{ number_format($content['post_a']['likes']) }}</td>
                        <td style="padding-right:12px;"><span style="color:#b7ff00;">Comments</span>&nbsp;{{ number_format($content['post_a']['comments']) }}</td>
                        <td><span style="color:#b7ff00;">Shares</span>&nbsp;{{ number_format($content['post_a']['shares']) }}</td>
                    </tr>
                </table>
                {!! $charts['post_a'] !!}
                <span class="slbl" style="margin-top:8px;display:block;">Content Types Analysis:</span>
                <table style="width:100%;border-collapse:collapse;margin-top:4px;">
                    <tr>
                        <td style="font-size:13px;padding-right:14px;vertical-align:middle;">{{ $content['post_a']['type'] }}</td>
                        <td style="vertical-align:middle;">
                            <div class="eb">{{ $content['post_a']['engagement_rate'] }}</div>
                            <div class="el">Engagement Rate</div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Post B --}}
            <td style="padding-left:16px;vertical-align:top;">
                <span class="slbl">Top Posts:</span>
                <div class="pn">Post B</div>
                <table style="width:100%;border-collapse:collapse;margin-bottom:7px;font-size:9px;color:#bfc6d2;">
                    <tr>
                        <td style="padding-right:12px;"><span style="color:#b7ff00;">Likes</span>&nbsp;{{ number_format($content['post_b']['likes']) }}</td>
                        <td style="padding-right:12px;"><span style="color:#b7ff00;">Comments</span>&nbsp;{{ number_format($content['post_b']['comments']) }}</td>
                        <td><span style="color:#b7ff00;">Shares</span>&nbsp;{{ number_format($content['post_b']['shares']) }}</td>
                    </tr>
                </table>
                {!! $charts['post_b'] !!}
                <span class="slbl" style="margin-top:8px;display:block;">Content Types Analysis:</span>
                <table style="width:100%;border-collapse:collapse;margin-top:4px;">
                    <tr>
                        <td style="font-size:13px;padding-right:14px;vertical-align:middle;">{{ $content['post_b']['type'] }}</td>
                        <td style="vertical-align:middle;">
                            <div class="eb">{{ $content['post_b']['engagement_rate'] }}</div>
                            <div class="el">Engagement Rate</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="ftr"><table><tr>
        <td>{{ $company }}</td><td class="fc">Monthly Performance Report</td><td class="fr">{{ $website }}</td>
    </tr></table></div>
</div>

{{-- ════════════════════════════════════════════════════════
     SLIDE 7 — Campaign Analysis
════════════════════════════════════════════════════════════ --}}
<div class="page">
    <div class="sh"><span class="italic">C</span>ampaign <span class="italic">A</span>nalysis</div>
    <hr class="div">

    <table class="L">
        <tr>
            <td style="width:62%;border-right:1px solid #222;padding-right:20px;vertical-align:top;">
                <span class="slbl">Campaign Performance</span>
                <table style="width:100%;border-collapse:collapse;margin-top:6px;">
                    <tr>
                        @foreach(['x' => 'Campaign X', 'y' => 'Campaign Y'] as $key => $label)
                        <td style="width:50%;padding-right:14px;vertical-align:top;">
                            <div class="cn">{{ $label }}</div>
                            <div class="cl">Ad Spend:</div>
                            <div class="cv">{{ $campaigns[$key]['ad_spend'] }}</div>
                            <div class="cl">Conversions:</div>
                            <div class="cv">{{ $campaigns[$key]['conversions'] }}</div>
                            <div class="cl">Reach:</div>
                            <div class="cv">{{ $campaigns[$key]['reach'] }}</div>
                            <div class="cl">ROI:</div>
                            <div class="cv">{{ $campaigns[$key]['roi'] }}</div>
                        </td>
                        @endforeach
                    </tr>
                </table>
            </td>
            <td style="padding-left:20px;vertical-align:top;">
                <span class="slbl">ROI Analysis Chart</span>
                <div style="margin-top:6px;">{!! $charts['roi'] !!}</div>
            </td>
        </tr>
    </table>

    <div class="ftr"><table><tr>
        <td>{{ $company }}</td><td class="fc">Monthly Performance Report</td><td class="fr">{{ $website }}</td>
    </tr></table></div>
</div>

{{-- ════════════════════════════════════════════════════════
     SLIDE 8 — Sentiment Analysis
════════════════════════════════════════════════════════════ --}}
<div class="page">
    <div class="sh"><span class="italic">S</span>entiment <span class="italic">A</span>nalysis</div>
    <hr class="div">

    <table class="L">
        <tr>
            <td style="width:48%;border-right:1px solid #222;padding-right:26px;vertical-align:top;">
                <span class="slbl">Sentiment Overview</span>
                @foreach([
                    ['Positive sentiment', $sentiment['positive']],
                    ['Neutral sentiment',  $sentiment['neutral']],
                    ['Negative sentiment', $sentiment['negative']],
                ] as $row)
                <table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
                    <tr>
                        <td style="width:128px;" class="ssl">{{ $row[0] }}</td>
                        <td style="width:24px;color:#b7ff00;font-size:14px;text-align:center;">-&gt;</td>
                        <td class="ssp">{{ $row[1] }}%</td>
                    </tr>
                </table>
                @endforeach
            </td>
            <td style="padding-left:26px;vertical-align:top;">
                <span class="slbl">Key Sentiments</span>
                @foreach($sentiment['comments'] as $i => $c)
                <table style="width:100%;border-collapse:collapse;margin-bottom:13px;">
                    <tr>
                        <td style="width:40px;vertical-align:middle;">
                            <div style="width:33px;height:33px;background:#2a2a2a;text-align:center;line-height:33px;font-size:12px;color:#fff;">
                                @if($i===0)F @elseif($i===1)N @else M @endif
                            </div>
                        </td>
                        <td style="padding-left:8px;vertical-align:middle;">
                            <div class="ct2">{{ $c['type'] }}</div>
                            <div class="ctx">{{ $c['text'] }}</div>
                        </td>
                    </tr>
                </table>
                @endforeach
            </td>
        </tr>
    </table>

    <div class="ftr"><table><tr>
        <td>{{ $company }}</td><td class="fc">Monthly Performance Report</td><td class="fr">{{ $website }}</td>
    </tr></table></div>
</div>

{{-- ════════════════════════════════════════════════════════
     SLIDE 9 — Thank You
════════════════════════════════════════════════════════════ --}}
<div class="page">
    <div style="font-size:8px;color:#b7ff00;margin-bottom:4px;">{{ $company }}</div>
    <div style="font-size:54px;font-weight:300;margin:8px 0 20px;line-height:1.0;">
        <span class="italic">T</span>hank <span class="italic">Y</span>ou
    </div>
    <table class="L">
        <tr>
            <td style="width:25%;padding-right:14px;vertical-align:top;">
                <div class="col">Address</div>
                <div class="cov">{{ $contact['address'] }}</div>
            </td>
            <td style="width:25%;padding-right:14px;vertical-align:top;">
                <div class="col">Social Media &amp; Website</div>
                <div class="cov">{{ $contact['social'] }}<br>{{ $website }}</div>
            </td>
            <td style="width:25%;padding-right:14px;vertical-align:top;">
                <div class="col">Phone</div>
                <div class="cov">{{ $contact['phone'] }}</div>
            </td>
            <td style="width:25%;vertical-align:top;">
                <div class="col">Email</div>
                <div class="cov">{{ $contact['email'] }}</div>
            </td>
        </tr>
    </table>

    <div class="ftr"><table><tr>
        <td>{{ $company }}</td><td class="fc">Monthly Performance Report</td><td class="fr">{{ $website }}</td>
    </tr></table></div>
</div>

</body>
</html>