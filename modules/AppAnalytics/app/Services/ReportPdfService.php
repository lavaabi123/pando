<?php

namespace Modules\AppAnalytics\Services;

use Illuminate\Support\Facades\Http;

class ReportPdfService
{
    public function getData(): array
    {
        return [
            'month'     => 'January 2026',
            'presenter' => 'Hannah Morales',
            'company'   => 'Rimberio Marketing',
            'website'   => 'www.reallygreatsite.com',

            'analytics' => [
                'impressions'       => '12,260',
                'impressions_delta' => '+45.2%',
                'engagement_rate'   => '8.6%',
                'clicks'            => '596',
                'members'           => '6,272',
                'page_views'        => '800',
            ],

            'performance' => [
                'unique_visitors'       => '356',
                'unique_visitors_delta' => '+51.5%',
                'comments'              => '68',
                'comments_delta'        => '+466',
                'reactions'             => '385',
                'reactions_delta'       => '+52.2%',
                'new_followers'         => '42',
                'new_followers_delta'   => '+44.8%',
            ],

            'audience' => [
                'industries' => [
                    ['name' => 'Financial Services',            'value' => 40, 'color' => '#a8c93a'],
                    ['name' => 'Gambling Facilities & Casinos', 'value' => 30, 'color' => '#c8ff00'],
                    ['name' => 'IT Services',                   'value' => 15, 'color' => '#8fa347'],
                    ['name' => 'Technology & Information',      'value' => 10, 'color' => '#6f7a50'],
                    ['name' => 'Software Development',          'value' => 10, 'color' => '#5c6548'],
                    ['name' => 'Hospitality',                   'value' => 10, 'color' => '#7c845a'],
                ],
            ],

            'content' => [
                'post_a' => ['likes' => 3000, 'comments' => 1200, 'shares' => 1500, 'engagement_rate' => '5.0%', 'type' => 'Images'],
                'post_b' => ['likes' => 950,  'comments' => 250,  'shares' => 100,  'engagement_rate' => '4.0%', 'type' => 'Videos'],
            ],

            'campaigns' => [
                'x' => ['ad_spend' => '$5,000', 'conversions' => '200', 'reach' => '50,000', 'roi' => '4:1', 'roi_value' => 4],
                'y' => ['ad_spend' => '$3,000', 'conversions' => '150', 'reach' => '30,000', 'roi' => '5:1', 'roi_value' => 5],
            ],

            'sentiment' => [
                'positive' => 70,
                'neutral'  => 20,
                'negative' => 10,
                'comments' => [
                    ['type' => 'Positive Comment',   'text' => '"Great content!"'],
                    ['type' => 'Neutral Comment',    'text' => '"Interesting Post"'],
                    ['type' => 'Negative Sentiment', 'text' => '"Could be better"'],
                ],
            ],

            'contact' => [
                'address' => '123 Anywhere St., Any City, ST 12345',
                'phone'   => '+123 - 456 - 7890',
                'email'   => 'hello@reallygreatsite.com',
                'social'  => '@reallygreatsite',
            ],

            'charts' => [
                'trend'  => self::lineTrend([4000, 8000, 7800, 5800], ['May', 'June', 'July', 'Aug']),
                'donut'  => self::donut([
                    ['value' => 40, 'color' => '#a8c93a'],
                    ['value' => 30, 'color' => '#c8ff00'],
                    ['value' => 15, 'color' => '#8fa347'],
                    ['value' => 10, 'color' => '#6f7a50'],
                    ['value' => 10, 'color' => '#5c6548'],
                    ['value' => 10, 'color' => '#7c845a'],
                ]),
                'post_a' => self::hBar([3000, 1500, 1200], ['Like', 'Share', 'Comment'], 3000),
                'post_b' => self::hBar([950,  250,  100],  ['Like', 'Comment', 'Share'],  950),
                'roi'    => self::vBar([4, 5], ['Camp. X', 'Camp. Y'], 6),
            ],
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function hex2rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private static function gc(\GdImage $img, string $hex): int
    {
        [$r, $g, $b] = self::hex2rgb($hex);
        return imagecolorallocate($img, $r, $g, $b);
    }

    private static function tag(\GdImage $img, int $w, int $h): string
    {
        ob_start();
        imagepng($img);
        $b64 = base64_encode(ob_get_clean());
        imagedestroy($img);
        return "<img src=\"data:image/png;base64,{$b64}\" width=\"{$w}\" height=\"{$h}\" style=\"display:block;\"/>";
    }

    /** Catmull-Rom interpolation for smooth curves */
    private static function catmull(float $p0, float $p1, float $p2, float $p3, float $t): float
    {
        return 0.5 * (
            (2 * $p1) +
            (-$p0 + $p2) * $t +
            (2*$p0 - 5*$p1 + 4*$p2 - $p3) * $t * $t +
            (-$p0 + 3*$p1 - 3*$p2 + $p3) * $t * $t * $t
        );
    }

    /**
     * Smooth line-trend: draw at 2x resolution with Catmull-Rom spline,
     * then resample down for natural anti-aliasing.
     */
    public static function lineTrend(array $data, array $labels): string
    {
        $W = 560; $H = 160;
        $pL = 14; $pR = 14; $pT = 18; $pB = 36;

        $img  = imagecreatetruecolor($W, $H);
        $bg   = self::gc($img, '#0b0b0b');
        $grid = self::gc($img, '#1e1e1e');
        $lime = self::gc($img, '#b6ff00');
        $txtc = self::gc($img, '#9a9a9a');

        imagefill($img, 0, 0, $bg);

        for ($g = 1; $g <= 3; $g++) {
            $gy = (int)($pT + ($g / 4) * ($H - $pT - $pB));
            imageline($img, $pL, $gy, $W - $pR, $gy, $grid);
        }

        $max = max($data) ?: 1;
        $n   = count($data);
        $sx  = ($W - $pL - $pR) / ($n - 1);

        $pts = [];
        foreach ($data as $i => $v) {
            $pts[] = [
                (int)round($pL + $i * $sx),
                (int)round($pT + (1 - $v / $max) * ($H - $pT - $pB)),
            ];
        }

        // Catmull-Rom smooth curve
        $steps = 60;
        imagesetthickness($img, 3);
        for ($i = 0; $i < $n - 1; $i++) {
            $p0 = $pts[max(0, $i-1)];
            $p1 = $pts[$i];
            $p2 = $pts[$i+1];
            $p3 = $pts[min($n-1, $i+2)];
            for ($s = 0; $s < $steps; $s++) {
                $t1 = $s / $steps;
                $t2 = ($s + 1) / $steps;
                $x1 = (int)self::catmull($p0[0], $p1[0], $p2[0], $p3[0], $t1);
                $y1 = (int)self::catmull($p0[1], $p1[1], $p2[1], $p3[1], $t1);
                $x2 = (int)self::catmull($p0[0], $p1[0], $p2[0], $p3[0], $t2);
                $y2 = (int)self::catmull($p0[1], $p1[1], $p2[1], $p3[1], $t2);
                imageline($img, $x1, $y1, $x2, $y2, $lime);
            }
        }
        imagesetthickness($img, 1);

        foreach ($pts as $pt) {
            imagefilledellipse($img, $pt[0], $pt[1], 10, 10, $lime);
        }

        foreach ($labels as $i => $lbl) {
            $tx = (int)round($pL + $i * $sx) - (int)(strlen($lbl) * 3.5);
            imagestring($img, 2, $tx, $H - $pB + 8, $lbl, $txtc);
        }

        $out = imagecreatetruecolor(280, 80);
        imagecopyresampled($out, $img, 0, 0, 0, 0, 280, 80, $W, $H);
        imagedestroy($img);
        return self::tag($out, 280, 80);
    }

    /** Donut chart — 2x resolution for crispness */
    public static function donut(array $slices): string
    {
        $W = 300; $H = 300;
        $cx = 150; $cy = 150; $or = 130; $ir = 74;

        $img = imagecreatetruecolor($W, $H);
        $bg  = self::gc($img, '#000000');
        imagefill($img, 0, 0, $bg);

        $total = array_sum(array_column($slices, 'value'));
        $angle = -90;
        foreach ($slices as $s) {
            $sweep = (int)round(($s['value'] / $total) * 360);
            $col   = self::gc($img, $s['color']);
            imagefilledarc($img, $cx, $cy, $or*2, $or*2, $angle, $angle+$sweep, $col, IMG_ARC_PIE);
            $angle += $sweep;
        }
        $hole = self::gc($img, '#000000');
        imagefilledellipse($img, $cx, $cy, $ir*2, $ir*2, $hole);

        $out = imagecreatetruecolor(150, 150);
        imagecopyresampled($out, $img, 0, 0, 0, 0, 150, 150, $W, $H);
        imagedestroy($img);
        return self::tag($out, 150, 150);
    }

    /** Horizontal bar chart — 2x resolution */
    public static function hBar(array $values, array $labels, int $max): string
    {
        $W = 560; $H = 180;
        $pL = 110; $pR = 20; $pT = 12;
        $barH = 28; $gap = 18;

        $img  = imagecreatetruecolor($W, $H);
        $bg   = self::gc($img, '#000000');
        $lime = self::gc($img, '#a8e000');
        $dim  = self::gc($img, '#1e1e1e');
        $tc   = self::gc($img, '#aaaaaa');
        $wc   = self::gc($img, '#ffffff');

        imagefill($img, 0, 0, $bg);

        foreach ($values as $i => $v) {
            $y    = $pT + $i * ($barH + $gap);
            $barW = (int)max(4, (($v / $max) * ($W - $pL - $pR)));
            // Track
            imagefilledrectangle($img, $pL, $y, $W - $pR, $y + $barH, $dim);
            // Bar
            imagefilledrectangle($img, $pL, $y, $pL + $barW, $y + $barH, $lime);
            // Label
            $lx = $pL - (strlen($labels[$i]) * 12) - 8;
            imagestring($img, 3, max(0, $lx), $y + 7, $labels[$i], $tc);
            // Value
            imagestring($img, 2, $pL + $barW + 6, $y + 8, number_format($v), $wc);
        }

        $out = imagecreatetruecolor(280, 90);
        imagecopyresampled($out, $img, 0, 0, 0, 0, 280, 90, $W, $H);
        imagedestroy($img);
        return self::tag($out, 280, 90);
    }

    /** Vertical bar chart — 2x resolution */
    public static function vBar(array $values, array $labels, int $max): string
    {
        $W = 400; $H = 260;
        $pL = 20; $pR = 20; $pT = 30; $pB = 40;
        $barW = 80; $gap = 60;

        $img  = imagecreatetruecolor($W, $H);
        $bg   = self::gc($img, '#000000');
        $lime = self::gc($img, '#b7ff00');
        $grid = self::gc($img, '#1c1c1c');
        $tc   = self::gc($img, '#aaaaaa');
        $wc   = self::gc($img, '#ffffff');

        imagefill($img, 0, 0, $bg);

        for ($g = 1; $g <= 4; $g++) {
            $gy = (int)($pT + ($g / 5) * ($H - $pT - $pB));
            imageline($img, $pL, $gy, $W - $pR, $gy, $grid);
        }

        foreach ($values as $i => $v) {
            $x  = $pL + $i * ($barW + $gap);
            $bh = (int)(($v / $max) * ($H - $pT - $pB));
            $y  = $H - $pB - $bh;

            imagefilledrectangle($img, $x, $y, $x + $barW, $H - $pB, $lime);

            $vStr = (string)$v;
            imagestring($img, 3, $x + (int)(($barW - strlen($vStr)*8)/2), $y - 22, $vStr, $wc);
            imagestring($img, 2, $x + (int)(($barW - strlen($labels[$i])*7)/2), $H - $pB + 6, $labels[$i], $tc);
        }

        $out = imagecreatetruecolor(200, 130);
        imagecopyresampled($out, $img, 0, 0, 0, 0, 200, 130, $W, $H);
        imagedestroy($img);
        return self::tag($out, 200, 130);
    }
}