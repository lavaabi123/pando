<?php
ini_set('memory_limit', '1024M');
set_time_limit(300);

// ── Emoji → <img> helper ─────────────────────────────────────────────────────
if (!defined('EMOJI_CACHE_DIR')) {
    define('EMOJI_CACHE_DIR', storage_path('app/public/emojis') . '/');
}
if (!function_exists('mb_ord')) {
    function mb_ord($ch, $enc = 'UTF-8') {
        $u = mb_convert_encoding($ch, 'UCS-4BE', $enc);
        $b = unpack('N', $u); return $b ? $b[1] : null;
    }
}
function hex_pad_e(string $hex): string {
    return (strlen($hex) < 4) ? str_pad($hex, 4, '0', STR_PAD_LEFT) : $hex;
}
function http_fetch_emoji(string $url, int $timeout = 6): ?string {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,
            CURLOPT_CONNECTTIMEOUT=>$timeout,CURLOPT_TIMEOUT=>$timeout,
            CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_USERAGENT=>'EmojiFetcher/1.2']);
        $data = curl_exec($ch);
        $ok = ($data !== false) && (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE) < 300;
        curl_close($ch);
        if ($ok && strlen($data) > 100) return $data;
    }
    $ctx = stream_context_create(['http'=>['timeout'=>$timeout,'header'=>"User-Agent: EmojiFetcher/1.2\r\n"],'ssl'=>['verify_peer'=>false]]);
    $data = @file_get_contents($url, false, $ctx);
    return ($data !== false && strlen($data) > 100) ? $data : null;
}
function codepoint_png_data_uri_e(string $hex): ?string {
    @mkdir(EMOJI_CACHE_DIR, 0755, true);
    $cache = EMOJI_CACHE_DIR . strtolower($hex) . '.png';
    if (is_file($cache)) { $b = @file_get_contents($cache); if ($b && strlen($b)>100) return 'data:image/png;base64,'.base64_encode($b); }
    $parts = array_map('hex_pad_e', explode('-', strtolower($hex)));
    $noto  = 'emoji_u'.implode('_',$parts);
    $notoNv= preg_replace('/_+/','_',trim(preg_replace('/(^|_)fe0f(?=_|$)/','',$noto),'_'));
    $tw    = preg_replace('/-+/','-',trim(preg_replace('/(^|-)?fe0f(?=-|$)/','',strtolower($hex)),'-'));
    foreach ([
        "https://cdn.jsdelivr.net/gh/googlefonts/noto-emoji@main/png/128/{$noto}.png",
        "https://cdn.jsdelivr.net/gh/googlefonts/noto-emoji@main/png/128/{$notoNv}.png",
        "https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/{$tw}.png",
        "https://cdn.jsdelivr.net/gh/twitter/twemoji@latest/assets/72x72/{$tw}.png",
    ] as $url) {
        $bytes = http_fetch_emoji($url);
        if ($bytes) { @file_put_contents($cache,$bytes); return 'data:image/png;base64,'.base64_encode($bytes); }
    }
    return null;
}
function is_emoji_cp(int $cp): bool {
    return ($cp>=0x1F300&&$cp<=0x1FAFF)||($cp>=0x2600&&$cp<=0x27BF)
        ||($cp>=0x1F000&&$cp<=0x1F02F)||($cp>=0x2300&&$cp<=0x23FF)
        ||($cp>=0x1F1E6&&$cp<=0x1F1FF);
}
function emoji_to_img_l(string $text): string {
    $out=''; $len=mb_strlen($text,'UTF-8');
    for($i=0;$i<$len;$i++){
        $ch=mb_substr($text,$i,1,'UTF-8'); $cp=mb_ord($ch);
        if($cp===0xFE0F||$cp===0x200D){$out.=$ch;continue;}
        if($cp!==null&&is_emoji_cp($cp)){
            $hex=hex_pad_e(strtolower(dechex($cp)));
            $src=codepoint_png_data_uri_e($hex)??($cp<0x10000?codepoint_png_data_uri_e($hex.'-fe0f'):null);
            if($src){$out.='<img src="'.$src.'" alt="'.htmlspecialchars($ch,ENT_QUOTES,'UTF-8').'" style="width:14px;height:14px;vertical-align:middle;">';continue;}
        }
        $out.=$ch;
    }
    return $out;
}
// ─────────────────────────────────────────────────────────────────────────────

function getResizedBase64Image($url, $maxWidth = 300, &$cache = []) {
    if (isset($cache[$url])) return $cache[$url];
    $data = @file_get_contents($url);
    if (!$data) return '';
    $src = @imagecreatefromstring($data);
    if (!$src) return '';
    $width  = imagesx($src);
    $height = imagesy($src);
    $newWidth  = (int) min($width, $maxWidth);
    $newHeight = (int) round(($newWidth / $width) * $height);
    $resized = imagecreatetruecolor($newWidth, $newHeight);
    $isPng = (stripos($url, '.png') !== false);
    if ($isPng) {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
    }
    imagecopyresampled($resized, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    ob_start();
    if ($isPng) { imagepng($resized); $mime = 'image/png'; }
    else        { imagejpeg($resized, null, 75); $mime = 'image/jpeg'; }
    $b64 = base64_encode(ob_get_clean());
    imagedestroy($src); imagedestroy($resized);
    return $cache[$url] = 'data:' . $mime . ';base64,' . $b64;
}

function roundedBase64Image($base64, $size = 40) {
    $parts = explode(',', $base64);
    if (count($parts) < 2) return '';
    $src = @imagecreatefromstring(base64_decode($parts[1]));
    if (!$src) return '';
    $w = imagesx($src); $h = imagesy($src);
    $min = min($w,$h);
    $sq = imagecreatetruecolor($size,$size);
    imagesavealpha($sq, true);
    imagefill($sq, 0, 0, imagecolorallocatealpha($sq,0,0,0,127));
    imagecopyresampled($sq,$src,0,0,($w-$min)/2,($h-$min)/2,$size,$size,$min,$min);
    $mask = imagecreatetruecolor($size,$size);
    imagesavealpha($mask, true);
    imagefill($mask,0,0,imagecolorallocatealpha($mask,0,0,0,127));
    imagefilledellipse($mask,$size/2,$size/2,$size,$size,imagecolorallocatealpha($mask,0,0,0,0));
    $out = imagecreatetruecolor($size,$size);
    imagesavealpha($out, true);
    imagefill($out,0,0,imagecolorallocatealpha($out,0,0,0,127));
    for($x=0;$x<$size;$x++) for($y=0;$y<$size;$y++) {
        $a = (imagecolorat($mask,$x,$y)>>24)&0x7F;
        $c = imagecolorsforindex($sq, imagecolorat($sq,$x,$y));
        imagesetpixel($out,$x,$y,imagecolorallocatealpha($out,$c['red'],$c['green'],$c['blue'],$a));
    }
    ob_start(); imagepng($out); $img = ob_get_clean();
    imagedestroy($src); imagedestroy($sq); imagedestroy($mask); imagedestroy($out);
    return 'data:image/png;base64,' . base64_encode($img);
}

function forceBreakWords(string $html, int $maxLength = 20): string {
    if (empty(trim($html))) return $html;
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($doc);
    foreach ($xpath->query('//text()') as $node) {
        if (trim($node->nodeValue) !== '') {
            $node->nodeValue = preg_replace_callback('/[^\s]{'.$maxLength.',}/u',
                fn($m) => wordwrap($m[0], $maxLength, "\u{200B}", true), $node->nodeValue);
        }
    }
    $out = $doc->saveHTML($doc->getElementsByTagName('body')->item(0));
    return str_replace(['<body>','</body>'], '', $out);
}

$iconCache = [];

// Logo
$logoPath   = public_path('assets/img/hlogo.png');
$logoB64    = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

// Header background
$headerBgPath = public_path('assets/img/headerbg.jpg');
$headerBgB64  = file_exists($headerBgPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($headerBgPath)) : '';

// Page background
$pdfBgPath = public_path('assets/img/pdfbg.jpg');
$pdfBgB64  = file_exists($pdfBgPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($pdfBgPath)) : '';

// Social icons
$socialIconMap = [
    'facebook'=>'https://itspando.com/assets/img/face.png',
    'twitter'=>'https://itspando.com/assets/img/twitter.png',
    'instagram'=>'https://itspando.com/assets/img/instagram.png',
    'linkedin'=>'https://itspando.com/assets/img/linkedin.png',
    'pinterest'=>'https://itspando.com/assets/img/pinterest.png',
    'google_business'=>'https://itspando.com/assets/img/fgroup.png',
];
$socialIcons = [];
foreach ($socialIconMap as $k => $u) {
    $socialIcons[$k] = getResizedBase64Image($u, 40, $iconCache);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
html { margin:0; color:#000; background:#fff; }
@page { margin:0; padding:0; }
body {
    font-family: Arial, sans-serif;
    font-size: 11pt;
    color: #000;
    background: #fff;
    <?php if ($pdfBgB64): ?>
    background-image: url(<?php echo $pdfBgB64; ?>);
    background-repeat: no-repeat;
    background-size: contain;
    background-position: bottom center;
    <?php endif; ?>
}
table.outer { width:100%; border-spacing:10pt; border-collapse:separate; table-layout:fixed; }
td { word-break:break-all; overflow-wrap:break-word; }
</style>
</head>
<body>

<!-- HEADER -->
<table width="100%" cellpadding="0" cellspacing="0"
    style="<?php echo $headerBgB64 ? 'background-image:url('.$headerBgB64.');' : 'background-color:#2ecc71;' ?>background-size:cover;padding:15px 25px 15px 0;">
    <tr>
        <td style="background:transparent;"><img src="<?php echo $logoB64; ?>" style="width:40%;"/></td>
        <td align="right" style="background:transparent;">
            <h1 style="color:#fff;margin:0;font-size:21px;font-weight:bold;">Brand: <?php echo htmlspecialchars($brand_name); ?></h1>
            <p style="color:#fff;margin:0;font-size:16px;font-weight:bold;">Created on: <?php echo date("M d, Y"); ?></p>
        </td>
    </tr>
</table>

<!-- POSTS GRID -->
<table class="outer">
<?php
$num = count($result);
$i   = 0;
foreach ($result as $key => $value):
    $data = json_decode($value->data);
    if ($i % 3 === 0) echo '<tr>';
?>
<td style="width:33.33%;max-width:33.33%;vertical-align:top;background:#fff;border:1px solid #dcdcdc;border-radius:20px;box-shadow:2px 3px 5px #00000022;padding:10px;">

    <!-- Post header -->
    <table style="width:100%;border-spacing:0;font-size:11px;color:#888;table-layout:fixed;">
        <tr>
            <td style="width:65%;vertical-align:middle;">
                <?php echo ($key+1).'. '; echo !empty($value->time_post) ? date("M d, Y h:i a", $value->time_post) : 'Yet to Schedule'; ?>
            </td>
            <td style="width:35%;vertical-align:middle;text-align:right;">
                <?php
// Only render avatar if it exists and loads — try local path first, HTTP fallback
if (!empty($value->avatar)) {
    $avatarData = false;
    $avatarExt  = 'jpeg';
    if (str_starts_with($value->avatar, 'http')) {
        // Already a full URL
        $avatarData = @file_get_contents($value->avatar);
        $avatarExt  = pathinfo($value->avatar, PATHINFO_EXTENSION) ?: 'jpeg';
    } else {
        // Try local storage path first (avoids HTTP fetch being blocked)
        $localAvatar = storage_path('app/public/' . ltrim($value->avatar, '/'));
        if (file_exists($localAvatar)) {
            $avatarData = file_get_contents($localAvatar);
            $avatarExt  = pathinfo($localAvatar, PATHINFO_EXTENSION) ?: 'jpeg';
        } else {
            // Fallback to HTTP URL
            $httpPath   = url('storage/' . ltrim($value->avatar, '/'));
            $avatarData = @file_get_contents($httpPath);
            $avatarExt  = pathinfo($httpPath, PATHINFO_EXTENSION) ?: 'jpeg';
        }
    }
    if ($avatarData) {
        $base64 = 'data:image/' . $avatarExt . ';base64,' . base64_encode($avatarData);
        $avatarImg = '<img src="' . roundedBase64Image($base64, 40) . '" width="20" height="20" style="vertical-align:middle;" />';
    } else {
        $avatarImg = '';
    }
}

$network    = strtolower($value->social_network ?? '');
$networkKey = $network;
if (strpos($networkKey, 'google_business') !== false) $networkKey = 'google_business';
if ($networkKey === 'x') $networkKey = 'twitter';
$socialIconImg = isset($socialIcons[$networkKey])
    ? '<img src="' . $socialIcons[$networkKey] . '" width="10" height="10" style="position:absolute;bottom:0;right:0;"/>'
    : '';

// Wrap avatar + social badge in a relative-positioned span so badge sits at bottom-right of avatar
if (!empty($avatarImg)) {
    echo '<span style="position:relative;display:inline-block;width:20px;height:20px;vertical-align:middle;">'
        . $avatarImg
        . $socialIconImg
        . '</span>';
} elseif (!empty($socialIconImg)) {
    echo $socialIconImg;
}

if (($value->social_networks_count ?? 0) > 1) {
    echo '<span style="vertical-align:middle;">&nbsp;and ' . ($value->social_networks_count - 1) . ' more</span>';
}
?>	
            </td>
        </tr>
    </table>

    <!-- Caption -->
    <table style="width:100%;border-spacing:0;table-layout:fixed;">
        <tr>
            <td style="color:#888;border-radius:8px;font-size:11px;padding:0 0 10px;background:#fff;vertical-align:top;">
                <?php
                $caption = $data->caption ?? '';
                $caption = str_replace(
                    ["\u{2014}","\u{2013}","\u{2012}","\u{2018}","\u{2019}","\u{201C}","\u{201D}","\u{2026}","\u{00A0}"],
                    ['--','-','-',"'","'",'"','"','...',' '], $caption);
                $caption = mb_convert_encoding($caption, 'UTF-8', 'UTF-8');
                if (!empty(trim($caption))):
                ?>
                <p style="padding:8px;font-size:11px;word-break:break-all;overflow-wrap:break-word;margin-top:10px">
                    <?php echo forceBreakWords(emoji_to_img_l(nl2br(htmlspecialchars($caption, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false))), 15); ?>
                </p>
                <?php endif; ?>

                <!-- IMAGES -->
                <?php
/******************************************************************
 * 1.  Collect every media item (image or video thumb) into $pics
 ******************************************************************/
$pics = [];
if (!empty($data->medias)) {
    foreach ($data->medias as $media) {
        
        // Use local file path for better reliability
        $localPath = storage_path('app/public/' . ltrim($media, '/'));
        
        // Build URL as fallback
        $mediaPath = file_exists($localPath) ? $localPath : url('storage/' . ltrim($media, '/'));

        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $media)) {
            $b = getResizedBase64Image($mediaPath, 400, $iconCache);
            if ($b) $pics[] = $b;
            continue;
        }

        // Video - pick existing thumb
        $thumb = $data->facebook_thumbnail
              ?? $data->instagram_thumbnail
              ?? $data->linkedin_thumbnail
              ?? '';

        // Validate thumb is a real image URL
        if (!empty($thumb) && !preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $thumb)) {
            $thumb = '';
        }

        // ffmpeg fallback
        if ($thumb === '') {
    $localVideo = storage_path('app/public/' . ltrim($media, '/'));
    
    if (function_exists('shell_exec') && file_exists($localVideo)) {
        $ffmpeg     = env('ffmpeg_path', 'ffmpeg');
        $outputFile = storage_path('app/public/tmp_thumb_' . time() . '.jpg');
        
        // Linux/server command (no 2>NUL - that's Windows only)
        $cmd    = "\"{$ffmpeg}\" -i \"{$localVideo}\" -ss 00:00:01 -vframes 1 \"{$outputFile}\" 2>/dev/null";
        shell_exec($cmd);
        
        if (file_exists($outputFile)) {
            $thumb = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($outputFile));
            unlink($outputFile);
        }
    }
}

        if ($thumb !== '') {
            $pics[] = getResizedBase64Image($thumb, 400, $iconCache);
        }
    }
}

/******************************************************************
 * 2.  Nothing found? Show default link icon and quit.
 ******************************************************************/
if (count($pics) === 0 && !empty($value->link_icon)) {
    echo '<div style="text-align:center">
            <img src="' . getResizedBase64Image($value->link_icon, 400, $iconCache) . '" class="post-img">
          </div>';
    // cannot use return in blade — use else below instead
} else {

/******************************************************************
 * 3.  Print the gallery
 ******************************************************************/
    $totalPics  = count($pics);

	if (!empty($pics)) {
    $totalPics  = count($pics);
    $cellWidth  = 220;

    // Layout based on count:
    // 1       → 1/row, cap height at 150px (portrait images)
    // 2       → 2/row
    // 3       → 3/row
    // 4       → 2/row (2×2 grid)
    // 5       → 3/row (2+3)
    // 6+      → 3/row
    if ($totalPics === 1) {
        $imgsPerRow = 1;
    } elseif ($totalPics === 4) {
        $imgsPerRow = 2;
    } else {
        $imgsPerRow = min($totalPics, 3);
    }

    $imgPx = (int) floor(($cellWidth - ($imgsPerRow * 6)) / $imgsPerRow);

    echo '<table width="100%" cellpadding="1" cellspacing="1"><tr>';
    foreach ($pics as $idx => $base64) {
        if ($idx > 0 && $idx % $imgsPerRow === 0) echo '</tr><tr>';

        // For single image only: cap height so tall portraits don't dominate
        if ($totalPics === 1) {
            $parts2 = explode(',', $base64, 2);
            $imgRes = isset($parts2[1]) ? @imagecreatefromstring(base64_decode($parts2[1])) : false;
            $imgH   = $imgRes ? imagesy($imgRes) : 0;
            $imgW   = $imgRes ? imagesx($imgRes) : 0;
            if ($imgRes) imagedestroy($imgRes);
            // If portrait and taller than wide, cap at 150px height
            $imgStyle = ($imgH > 150 && $imgH > $imgW)
                ? 'height:150px;width:auto;'
                : 'width:' . $imgPx . 'px;height:auto;';
        } else {
            $imgStyle = 'width:' . $imgPx . 'px;height:auto;';
        }

        echo '<td style="text-align:center;vertical-align:top;">
                <img src="' . $base64 . '" style="' . $imgStyle . '">
              </td>';
    }
    echo '</tr></table>';
}
}

?>
            </td>
        </tr>
    </table>

    <p style="font-weight:bold;font-size:10pt;margin:8px 0 4px;">Post Schedule:</p>
    <table style="border-spacing:0;background:#fff;font-size:9pt;border-collapse:collapse;width:100%;border:1pt solid #ccc;">
        <tr>
            <td style="text-align:center;padding:4pt 10pt;">
                <?php echo !empty($value->time_post) ? date("M d, Y h:i a", $value->time_post) : 'Yet to Schedule'; ?>
            </td>
        </tr>
    </table>

</td>
<?php
    if ($i % 3 === 2) echo '</tr>';
    $i++;
endforeach;
if ($num % 3 !== 0) {
    for ($k = 0; $k < 3 - ($num % 3); $k++) echo '<td style="width:33.33%;"></td>';
    echo '</tr>';
}
?>
</table>
</body>
</html>