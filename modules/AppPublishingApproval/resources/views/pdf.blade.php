<?php
ini_set('memory_limit', '1024M');
set_time_limit(300);

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
$avatarPath = null;

if (!empty($value->avatar)) {
    $path = str_starts_with($value->avatar, 'http') 
        ? $value->avatar 
        : url('storage/' . ltrim($value->avatar, '/'));
    $avatarData = @file_get_contents($path);
} else {
    $avatarData = false;
}

// Fallback to default image
if (!$avatarData) {
    $defaultPath = public_path('img/default.png');
    if (!file_exists($defaultPath)) {
        $defaultPath = public_path('assets/img/default.jpg');
    }
    $avatarData = file_exists($defaultPath) ? file_get_contents($defaultPath) : null;
    $type = 'png';
} else {
    $type = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpeg';
}
if ($avatarData) {
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($avatarData);
    echo '<img src="' . roundedBase64Image($base64, 40) . '" width="20" height="20" style="vertical-align:middle;" />';
}

$network = strtolower(str_replace('_', '', $value->social_network ?? ''));
if (isset($socialIcons[$network])) {
    echo '<img src="' . $socialIcons[$network] . '" style="width:10px;height:10px;position:absolute;"/>';
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
                    <?php echo forceBreakWords(nl2br(htmlspecialchars($caption)), 15); ?>
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
    $imgsPerRow = min($totalPics, 3);
    $imgPx      = floor(($cellWidth - ($imgsPerRow * 8)) / $imgsPerRow);

    // Dompdf works better with table layout than float
    echo '<table width="100%" cellpadding="2" cellspacing="2"><tr>';
    foreach ($pics as $idx => $base64) {
        if ($idx > 0 && $idx % $imgsPerRow === 0) echo '</tr><tr>';
        echo '<td style="text-align:center;">
                <img src="' . $base64 . '" style="width:' . $imgPx . 'px; height:auto;">
              </td>';
    }
    echo '</tr></table>';
}
}

?>
            </td>
        </tr>
        <tr>
            <td style="font-size:11px;padding:6px 0 4px;">
                <p style="font-weight:bold;font-size:10pt;margin:4px 0;">Post Schedule:</p>
            </td>
        </tr>
    </table>

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
