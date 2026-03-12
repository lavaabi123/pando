<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
/*
 * DomPDF A4 pixel reference (96 dpi):
 *   A4 width  = 794px  (210mm)
 *   A4 height = 1123px (297mm)
 *   Safe inner width = 794 - (50*2) = 694px
 *
 * Rules:
 *   - NO min-height on .page  → prevents blank overflow pages
 *   - NO % widths on root     → use explicit px
 *   - NO position:absolute    → DomPDF ignores it for flow
 *   - footer via margin-top   → safe flow-based spacing
 */

@page { margin:0; size:794px 1123px; }

* { margin:0; padding:0; box-sizing:border-box; }

html {
    width: 794px;
    background: #000;
}

body {
    width: 794px;
    background: #000;
    color: #fff;
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    line-height: 1.4;
}

/* ─── PAGE ───────────────────────────────────────────── */
.page {
    width: 794px;
    min-height: 1123px;   /* change */
    padding: 48px 50px 40px;
    background: #000;
    page-break-after: always; 
}
.page:last-child { page-break-after: auto; }

/* ─── UTILITIES ─────────────────────────────────────── */
.green  { color: #b7ff00; }
.script { font-family: Georgia, serif; font-style: italic; }

.divider {
    height: 1px;
    background: #2a2a2a;
    margin: 8px 0 14px;
    font-size: 0;
    line-height: 0;
}

.sec-title {
    color: #b7ff00;
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 6px;
}

/* ─── FOOTER ────────────────────────────────────────── */
/* flow-based: sits at bottom via top margin */
.page-footer {
    margin-top: 20px;
    border-top: 1px solid #1a1a1a;
    padding-top: 6px;
}
.footer-t      { width: 694px; border-collapse: collapse; }
.footer-t td   { font-size: 8px; color: #444; }
.footer-t .fr  { text-align: right; }

/* ═══ PAGE 1 — COVER ═══════════════════════════════════ */
.cover-top    { padding-top: 60px; }
.cover-eyebrow {
    font-size: 8px; color: #555;
    letter-spacing: 3px; text-transform: uppercase;
    margin-bottom: 24px;
}
.cover-title  { font-size: 56px; font-weight: 300; line-height: 1.05; }
.cover-green  { font-size: 56px; font-weight: 300; color: #b7ff00; margin-bottom: 12px; }
.cover-arrow  {
    display: inline-block;
    width: 28px; height: 28px;
    border: 1px solid #b7ff00; border-radius: 50%;
    text-align: center; line-height: 26px;
    color: #b7ff00; font-size: 14px;
    margin-top: 26px;
}
.cover-meta-t  { width: 694px; border-collapse: collapse; margin-top: 36px; }
.cover-meta-t td { font-size: 9px; color: #555; border-top: 1px solid #222; padding-top: 12px; vertical-align: top; }
.cover-date    { font-size: 14px; font-weight: 700; letter-spacing: 2px; color: #fff; margin-top: 4px; }

/* ═══ INNER PAGE HEADING ═══════════════════════════════ */
.page-brand   { font-size: 8px; color: #555; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 2px; }
.page-heading { font-size: 36px; font-weight: 700; line-height: 1; margin-bottom: 3px; }

/* ═══ PAGE 2 — OVERVIEW ════════════════════════════════ */
.ov-intro   { font-size: 11px; line-height: 1.65; color: #bbb; margin-bottom: 18px; width: 694px; }
.ov-intro b { color: #fff; }

.kpi-t      { width: 694px; border-collapse: collapse; margin-bottom: 18px; }
.kpi-t td   { vertical-align: top; width: 138px; padding-right: 6px; }
.kpi-lbl { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
.kpi-val { font-size: 24px; font-weight: 300; color: #fff; line-height: 1; }
.kpi-chg { font-size: 9px; color: #b7ff00; margin-top: 2px; }

.chart-full  { width: 694px; height: 160px; display: block; }

/* ═══ PAGE 3 — PERFORMANCE ═════════════════════════════ */
.perf-t        { width: 694px; border-collapse: collapse; }
.perf-stats    { width: 190px; vertical-align: top; padding-right: 16px; border-right: 1px solid #1e1e1e; }
.perf-charts   { vertical-align: top; padding-left: 16px; width: 488px; }

.stat-blk  { margin-bottom: 20px; }
.stat-lbl  { font-size: 9px; color: #888; margin-bottom: 1px; }
.stat-val  { font-size: 30px; font-weight: 300; line-height: 1; }
.stat-chg  { font-size: 9px; color: #b7ff00; margin-top: 1px; }

.mini-t        { width: 488px; border-collapse: collapse; }
.mini-t td     { width: 244px; vertical-align: top; padding: 0 8px 14px 0; }
.mini-t td.r   { padding-right: 0; }
.mini-lbl  { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px; }
.mini-img  { width: 236px; height: 80px; display: block; }

/* ═══ PAGE 4 — AUDIENCE ════════════════════════════════ */
.aud-t       { width: 694px; border-collapse: collapse; }
.aud-donut   { width: 240px; vertical-align: top; padding-right: 20px; }
.aud-legend  { vertical-align: top; width: 434px; }

.donut-img   { width: 190px; height: 190px; display: block; margin: 6px auto; }

.leg-t          { width: 434px; border-collapse: collapse; }
.leg-t tr td    { vertical-align: middle; padding: 7px 0; border-bottom: 1px solid #111; }
.leg-dot        { width: 16px; }
.leg-dot span   { display: inline-block; width: 12px; height: 12px; border-radius: 2px; }
.leg-name       { font-size: 11px; color: #ccc; padding: 0 8px; }
.leg-arr        { color: #b7ff00; font-size: 13px; padding: 0 8px; white-space: nowrap; }
.leg-pct        { font-size: 13px; font-weight: 700; width: 36px; text-align: right; }

/* ═══ PAGE 5 — CONTENT PERFORMANCE ════════════════════ */
.posts-t     { width: 694px; border-collapse: collapse; }
.post-l      { width: 337px; vertical-align: top; padding-right: 14px; }
.post-r      { width: 337px; vertical-align: top; padding-left: 14px; border-left: 1px solid #1e1e1e; }

.post-name   { font-size: 32px; font-weight: 300; margin: 4px 0 6px; line-height: 1; }
.post-icons  { font-size: 10px; color: #ccc; margin-bottom: 8px; }
.post-icons .g { color: #b7ff00; }
.post-cimg   { width: 323px; height: 80px; display: block; margin-bottom: 10px; }
.eng-big     { font-size: 46px; font-weight: 300; line-height: 1; color: #fff; }
.eng-sub     { font-size: 9px; color: #666; margin-top: 2px; }
.ctype-lbl   { font-size: 12px; color: #ccc; margin-bottom: 3px; }

/* ═══ PAGE 6 — CAMPAIGN ════════════════════════════════ */
.camp-t      { width: 694px; border-collapse: collapse; }
.camp-l      { width: 370px; vertical-align: top; padding-right: 14px; }
.camp-r      { vertical-align: top; padding-left: 14px; border-left: 1px solid #1e1e1e; width: 310px; }

.camp-cols   { width: 356px; border-collapse: collapse; margin-top: 10px; }
.camp-cols td { width: 178px; vertical-align: top; padding-right: 10px; }
.camp-name   { font-size: 18px; font-weight: 700; margin-bottom: 12px; }
.met-lbl     { font-size: 9px; color: #888; margin-bottom: 1px; }
.met-val     { font-size: 22px; font-weight: 300; line-height: 1; margin-bottom: 10px; }
.roi-img     { width: 296px; height: 170px; display: block; margin-top: 6px; }

/* ═══ PAGE 7 — SENTIMENT ═══════════════════════════════ */
.sent-t      { width: 694px; border-collapse: collapse; }
.sent-l      { width: 320px; vertical-align: top; padding-right: 20px; border-right: 1px solid #1e1e1e; }
.sent-r      { vertical-align: top; padding-left: 20px; width: 354px; }

.sent-row-t      { width: 310px; border-collapse: collapse; margin-bottom: 16px; }
.sent-row-t td   { vertical-align: middle; }
.sent-txt    { font-size: 12px; color: #bfc6d2; width: 150px; }
.sent-arr    { font-size: 16px; color: #b7ff00; padding: 0 12px; }
.sent-pct    { font-size: 38px; font-weight: 300; line-height: 1; }

.com-type  { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; margin-top: 18px; }
.com-quote { font-size: 14px; font-style: italic; }

/* ═══ PAGE 8 — THANK YOU ═══════════════════════════════ */
.ty-heading { font-size: 62px; font-weight: 300; font-family: Georgia, serif; font-style: italic; margin-top: 160px; margin-bottom: 24px; line-height: 1; }
.ty-t       { width: 694px; border-collapse: collapse; margin-top: 20px; }
.ty-t td    { width: 347px; vertical-align: top; padding-right: 20px; padding-top: 16px; }
.ty-lbl     { font-size: 8px; color: #b7ff00; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 5px; }
.ty-val     { font-size: 12px; color: #ccc; line-height: 1.7; }
</style>
</head>
<body>


<!-- ═══════════════════════════════════════════
     PAGE 1 — COVER
═══════════════════════════════════════════ -->
<div class="page">
    <div class="cover-top">
        <div class="cover-eyebrow">Social Media Report &bull; Rimberio Marketing</div>
        <div class="cover-title">M<span class="script">o</span>nthly<br><span class="script">P</span>erformance</div>
        <div class="cover-green">Report</div>
        <div class="cover-arrow">&#8594;</div>
        <table class="cover-meta-t"><tr>
            <td style="width:347px;">
                Presented by<br>
                <strong style="color:#fff;font-size:11px;">Hannah Morales</strong>
            </td>
            <td style="text-align:right;">
                <div class="cover-date">JANUARY 2026</div>
            </td>
        </tr></table>
    </div>

    <div style="margin-top:340px;">
        <div class="page-footer">
            <table class="footer-t"><tr>
                <td>Monthly Performance Report</td>
                <td class="fr">www.reallygreatsite.com</td>
            </tr></table>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     PAGE 2 — LINKEDIN ANALYTICS
═══════════════════════════════════════════ -->
<div class="page">
    <div class="page-brand">Rimberio Marketing</div>
    <div class="page-heading">LinkedIn <span class="script">A</span>nalytics</div>
    <div class="divider"></div>

    <div class="ov-intro">
        January delivered <b>strong organic growth</b> and engagement on LinkedIn for Passport Technology.
        Performance improved across impressions, engagement signals, and follower growth. Increased visibility,
        stronger comment interaction, and expanded reach into core industry audiences indicate meaningful
        traction in B2B positioning.
    </div>

    <table class="kpi-t"><tr>
        <td>
            <div class="kpi-lbl">Impressions</div>
            <div class="kpi-val">12,260</div>
            <div class="kpi-chg">(+45.2%)</div>
        </td>
        <td>
            <div class="kpi-lbl">Engagement Rate</div>
            <div class="kpi-val">8.6%</div>
        </td>
        <td>
            <div class="kpi-lbl">Clicks</div>
            <div class="kpi-val">596</div>
        </td>
        <td>
            <div class="kpi-lbl">Reach / Members</div>
            <div class="kpi-val">6,272</div>
        </td>
        <td>
            <div class="kpi-lbl">Page Views</div>
            <div class="kpi-val">800</div>
        </td>
    </tr></table>

    @if(!empty($charts['main']))
        <div class="sec-title">Performance Trend</div>
        <img class="chart-full" src="{{ $charts['main'] }}" />
    @endif

    <div class="page-footer">
        <table class="footer-t"><tr>
            <td>Monthly Performance Report</td>
            <td class="fr">www.reallygreatsite.com</td>
        </tr></table>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     PAGE 3 — LINKEDIN PERFORMANCE
═══════════════════════════════════════════ -->
<div class="page">
    <div class="page-brand">Rimberio Marketing</div>
    <div class="page-heading">LinkedIn <span class="script">P</span>erformance</div>
    <div class="divider"></div>

    <table class="perf-t"><tr>
        <td class="perf-stats">
            <div class="stat-blk">
                <div class="stat-lbl">Unique Visitors</div>
                <div class="stat-val">356</div>
                <div class="stat-chg">(+51.5%)</div>
            </div>
            <div class="stat-blk">
                <div class="stat-lbl">Comments</div>
                <div class="stat-val">68</div>
                <div class="stat-chg">(+466)</div>
            </div>
            <div class="stat-blk">
                <div class="stat-lbl">Reactions</div>
                <div class="stat-val">385</div>
                <div class="stat-chg">(+52.2%)</div>
            </div>
            <div class="stat-blk">
                <div class="stat-lbl">New Followers</div>
                <div class="stat-val">42</div>
                <div class="stat-chg">(+44.8%)</div>
            </div>
        </td>
        <td class="perf-charts">
            <table class="mini-t">
                <tr>
                    <td>
                        <div class="mini-lbl">Comments Trend</div>
                        @if(!empty($charts['comments']))
                            <img class="mini-img" src="{{ $charts['comments'] }}" />
                        @endif
                    </td>
                    <td class="r">
                        <div class="mini-lbl">Reactions Trend</div>
                        @if(!empty($charts['reactions']))
                            <img class="mini-img" src="{{ $charts['reactions'] }}" />
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="mini-lbl">Unique Visitors</div>
                        @if(!empty($charts['visitors']))
                            <img class="mini-img" src="{{ $charts['visitors'] }}" />
                        @endif
                    </td>
                    <td class="r">
                        <div class="mini-lbl">New Followers</div>
                        @if(!empty($charts['followers']))
                            <img class="mini-img" src="{{ $charts['followers'] }}" />
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr></table>

    <div class="page-footer">
        <table class="footer-t"><tr>
            <td>Monthly Performance Report</td>
            <td class="fr">www.reallygreatsite.com</td>
        </tr></table>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     PAGE 4 — LINKEDIN AUDIENCE
═══════════════════════════════════════════ -->
<div class="page">
    <div class="page-brand">Rimberio Marketing</div>
    <div class="page-heading">LinkedIn <span class="script">A</span>udience</div>
    <div class="divider"></div>
    <div class="sec-title">Top Industries</div>

    <table class="aud-t"><tr>
        <td class="aud-donut">
            @if(!empty($charts['industries']))
                <img class="donut-img" src="{{ $charts['industries'] }}" />
            @endif
        </td>
        <td class="aud-legend">
            <table class="leg-t">
                <tr>
                    <td class="leg-dot"><span style="background:#c8ff00;"></span></td>
                    <td class="leg-name">Gambling Facilities and Casinos</td>
                    <td class="leg-arr">&#8594;</td>
                    <td class="leg-pct">30%</td>
                </tr>
                <tr>
                    <td class="leg-dot"><span style="background:#a8c93a;"></span></td>
                    <td class="leg-name">Financial Services</td>
                    <td class="leg-arr">&#8594;</td>
                    <td class="leg-pct">40%</td>
                </tr>
                <tr>
                    <td class="leg-dot"><span style="background:#8fa347;"></span></td>
                    <td class="leg-name">IT Services</td>
                    <td class="leg-arr">&#8594;</td>
                    <td class="leg-pct">15%</td>
                </tr>
                <tr>
                    <td class="leg-dot"><span style="background:#6f7a50;"></span></td>
                    <td class="leg-name">Technology and Information</td>
                    <td class="leg-arr">&#8594;</td>
                    <td class="leg-pct">10%</td>
                </tr>
                <tr>
                    <td class="leg-dot"><span style="background:#5c6548;"></span></td>
                    <td class="leg-name">Software Development</td>
                    <td class="leg-arr">&#8594;</td>
                    <td class="leg-pct">10%</td>
                </tr>
                <tr>
                    <td class="leg-dot"><span style="background:#7c845a;"></span></td>
                    <td class="leg-name">Hospitality</td>
                    <td class="leg-arr">&#8594;</td>
                    <td class="leg-pct">10%</td>
                </tr>
            </table>
        </td>
    </tr></table>

    <div class="page-footer">
        <table class="footer-t"><tr>
            <td>Monthly Performance Report</td>
            <td class="fr">www.reallygreatsite.com</td>
        </tr></table>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     PAGE 5 — CONTENT PERFORMANCE
═══════════════════════════════════════════ -->
<div class="page">
    <div class="page-brand">Rimberio Marketing</div>
    <div class="page-heading"><span class="script">C</span>ontent <span class="script">P</span>erformance</div>
    <div class="divider"></div>

    <table class="posts-t"><tr>
        <td class="post-l">
            <div class="sec-title">Top Posts:</div>
            <div class="post-name">Post A</div>
            <div class="post-icons">
                <span class="g">&#9829;</span> 3,000 &nbsp;
                <span class="g">&#9679;</span> 1,200 &nbsp;
                <span class="g">&#10148;</span> 1,500
            </div>
            @if(!empty($charts['post_a']))
                <img class="post-cimg" src="{{ $charts['post_a'] }}" />
            @endif
            <div class="sec-title">Content Types Analysis:</div>
            <div class="ctype-lbl">Images</div>
            <div class="eng-big">5.0%</div>
            <div class="eng-sub">Engagement Rate</div>
        </td>
        <td class="post-r">
            <div class="sec-title">Top Posts:</div>
            <div class="post-name">Post B</div>
            <div class="post-icons">
                <span class="g">&#9829;</span> 950 &nbsp;
                <span class="g">&#9679;</span> 250 &nbsp;
                <span class="g">&#10148;</span> 100
            </div>
            @if(!empty($charts['post_b']))
                <img class="post-cimg" src="{{ $charts['post_b'] }}" />
            @endif
            <div class="sec-title">Content Types Analysis:</div>
            <div class="ctype-lbl">Videos</div>
            <div class="eng-big">4.0%</div>
            <div class="eng-sub">Engagement Rate</div>
        </td>
    </tr></table>

    <div class="page-footer">
        <table class="footer-t"><tr>
            <td>Monthly Performance Report</td>
            <td class="fr">www.reallygreatsite.com</td>
        </tr></table>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     PAGE 6 — CAMPAIGN ANALYSIS
═══════════════════════════════════════════ -->
<div class="page">
    <div class="page-brand">Rimberio Marketing</div>
    <div class="page-heading"><span class="script">C</span>ampaign <span class="script">A</span>nalysis</div>
    <div class="divider"></div>

    <table class="camp-t"><tr>
        <td class="camp-l">
            <div class="sec-title">Campaign Performance</div>
            <table class="camp-cols"><tr>
                <td>
                    <div class="camp-name">Campaign X</div>
                    <div class="met-lbl">Ad Spend:</div>
                    <div class="met-val">$5,000</div>
                    <div class="met-lbl">Conversions:</div>
                    <div class="met-val">200</div>
                    <div class="met-lbl">Reach:</div>
                    <div class="met-val">50,000</div>
                    <div class="met-lbl">ROI:</div>
                    <div class="met-val">4:1</div>
                </td>
                <td>
                    <div class="camp-name">Campaign Y</div>
                    <div class="met-lbl">Ad Spend:</div>
                    <div class="met-val">$3,000</div>
                    <div class="met-lbl">Conversions:</div>
                    <div class="met-val">150</div>
                    <div class="met-lbl">Reach:</div>
                    <div class="met-val">30,000</div>
                    <div class="met-lbl">ROI:</div>
                    <div class="met-val">5:1</div>
                </td>
            </tr></table>
        </td>
        <td class="camp-r">
            <div class="sec-title">ROI Analysis Chart</div>
            @if(!empty($charts['roi']))
                <img class="roi-img" src="{{ $charts['roi'] }}" />
            @endif
        </td>
    </tr></table>

    <div class="page-footer">
        <table class="footer-t"><tr>
            <td>Monthly Performance Report</td>
            <td class="fr">www.reallygreatsite.com</td>
        </tr></table>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     PAGE 7 — SENTIMENT ANALYSIS
═══════════════════════════════════════════ -->
<div class="page">
    <div class="page-brand">Rimberio Marketing</div>
    <div class="page-heading"><span class="script">S</span>entiment <span class="script">A</span>nalysis</div>
    <div class="divider"></div>

    <table class="sent-t"><tr>
        <td class="sent-l">
            <div class="sec-title">Sentiment Overview</div>
            <br/>
            <table class="sent-row-t">
                <tr>
                    <td class="sent-txt">Positive sentiment</td>
                    <td class="sent-arr">&#8594;</td>
                    <td class="sent-pct">70%</td>
                </tr>
            </table>
            <table class="sent-row-t">
                <tr>
                    <td class="sent-txt">Neutral sentiment</td>
                    <td class="sent-arr">&#8594;</td>
                    <td class="sent-pct">20%</td>
                </tr>
            </table>
            <table class="sent-row-t">
                <tr>
                    <td class="sent-txt">Negative sentiment</td>
                    <td class="sent-arr">&#8594;</td>
                    <td class="sent-pct">10%</td>
                </tr>
            </table>
        </td>
        <td class="sent-r">
            <div class="sec-title">Key Sentiments</div>
            <div class="com-type">Positive Comment</div>
            <div class="com-quote">"Great content!"</div>
            <div class="com-type">Neutral Comment</div>
            <div class="com-quote">"Interesting Post"</div>
            <div class="com-type">Negative Sentiment</div>
            <div class="com-quote">"Could be better"</div>
        </td>
    </tr></table>

    <div class="page-footer">
        <table class="footer-t"><tr>
            <td>Monthly Performance Report</td>
            <td class="fr">www.reallygreatsite.com</td>
        </tr></table>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     PAGE 8 — THANK YOU
═══════════════════════════════════════════ -->
<div class="page">
    <div class="ty-heading">Thank You</div>
    <div class="divider"></div>

    <table class="ty-t">
        <tr>
            <td>
                <div class="ty-lbl">Address</div>
                <div class="ty-val">123 Anywhere St.,<br/>Any City, ST 12345</div>
            </td>
            <td>
                <div class="ty-lbl">Social Media &amp; Website</div>
                <div class="ty-val">@reallygreatsite<br/>www.reallygreatsite.com</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="ty-lbl">Phone</div>
                <div class="ty-val">+123 &ndash; 456 &ndash; 7890</div>
            </td>
            <td>
                <div class="ty-lbl">Email</div>
                <div class="ty-val">hello@reallygreatsite.com</div>
            </td>
        </tr>
    </table>

    <div class="page-footer">
        <table class="footer-t"><tr>
            <td>Monthly Performance Report</td>
            <td class="fr">www.reallygreatsite.com</td>
        </tr></table>
    </div>
</div>


</body>
</html>