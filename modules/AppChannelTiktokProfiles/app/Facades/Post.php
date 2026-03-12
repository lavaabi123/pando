<?php

namespace Modules\AppChannelTiktokProfiles\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppChannels\Models\Accounts;
use Media;
use gimucco\TikTokLoginKit;
use gimucco\TikTokLoginKit\Connector;
use gimucco\TikTokLoginKit\response\PublishStatus;
use gimucco\TikTokLoginKit\uploads\VideoFromUrl;
use getID3;

class Post extends Facade
{
    protected static $tiktok;

    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    protected static function initTikTok($account)
    {
        $app_id       = get_option("tiktok_app_id", "");
        $app_secret   = get_option("tiktok_app_secret", "");
        $callback_url = module_url();

        self::$tiktok = new TikTokLoginKit\Connector($app_id, $app_secret, $callback_url);

        if ($account && $account->token) {
            $token = json_decode($account->token, true);
            if (!empty($token['access_token'])) {
                self::$tiktok->setToken($token['access_token']);
            }
        }
    }

    protected static function validator($post)
    {
        $errors  = [];
        $data    = json_decode($post->data, false);
        $medias  = $data->medias ?? [];
        $options = $data->options ?? (object)[];

        if (empty($medias)) {
            $errors[] = __("TikTok: Please select a video.");
        } else {
            $media = self::resolveMediaUrl((string)($medias[0] ?? ''));
            if (!self::mediaIsVideo($media)) {
                $errors[] = __("TikTok only supports video uploads.");
            } else {
                $getID3   = new getID3;
                $_vidLocalPath = self::urlToLocalPath(self::resolveMediaUrl((string)($medias[0] ?? '')));
                $fileInfo = ($_vidLocalPath && file_exists($_vidLocalPath)) ? $getID3->analyze($_vidLocalPath) : [];
                if (isset($fileInfo['video'])) {
                    $w        = $fileInfo['video']['resolution_x'] ?? 0;
                    $h        = $fileInfo['video']['resolution_y'] ?? 0;
                    $duration = $fileInfo['playtime_seconds'] ?? 0;
                    if ($duration > 180) {
                        $errors[] = __("TikTok: Video must be no longer than 3 minutes.");
                    }
                    if ($w && $h && $w / $h > 1) {
                        $errors[] = __("TikTok: Video must be vertical.");
                    }
                }
            }
        }

        if (empty($data->caption)) {
            $errors[] = __("TikTok: Please enter a caption for the video.");
        }

        return $errors;
    }

    protected static function post($post)
    {
        $account = $post->account;
        self::initTikTok($account);

        $tokenInfo = json_decode($account->token, true);
        if (!empty($tokenInfo['refresh_token'])) {
            try {
                $newToken = self::$tiktok->refreshToken($tokenInfo['refresh_token']);
                if ($newToken && $newToken->getAccessToken()) {
                    $account->token = json_encode([
                        "access_token"       => $newToken->getAccessToken(),
                        "refresh_token"      => $newToken->getRefreshToken(),
                        "expires_in"         => $newToken->getExpiresIn(),
                        "refresh_expires_in" => $newToken->getRefreshExpiresIn(),
                        "scope"              => implode(",", $newToken->getScope()),
                        "token_type"         => $newToken->getTokenType(),
                    ]);
                    $account->save();
                    self::$tiktok->setToken($newToken->getAccessToken());
                }
            } catch (\Exception $e) {
                Accounts::where("id", $account->id)->update(["status" => 0]);
                return [
                    "status"  => "error",
                    "message" => __("TikTok session expired"),
                    "type"    => $post->type,
                ];
            }
        }

        $data           = json_decode($post->data, false);
        $medias         = $data->medias ?? [];
        $caption        = $data->caption ?? '';
        $videoPath      = self::resolveMediaUrl((string)($medias[0] ?? ''));
        $tiktokSettings = $data->tiktok_settings ?? null;

        // ─── Resolve cover timestamp from custom thumbnail selection ──────────
        //
        // custom_thumbnail_index: 0–9 = user picked a generated frame
        //                          -1 = user picked from Media Library (no frame)
        //                        null = no thumbnail selected at all
        //
        // We need the actual video duration to be accurate. Use getID3 to read
        // it from the local file path.
        $thumbIndex = isset($data->custom_thumbnail_index) ? (int) $data->custom_thumbnail_index : -1;
        $coverMs    = self::_resolveCoverTimestampMs($medias[0] ?? null, $thumbIndex);
        // ──────────────────────────────────────────────────────────────────────

        $errors = self::validator($post);
        if ($errors) {
            return [
                "status"  => "error",
                "message" => implode(', ', $errors),
                "type"    => $post->type,
            ];
        }

        try {
            $uploadResult = self::uploadVideo($videoPath, $caption, $tiktokSettings, $coverMs);
            if ($uploadResult['status'] != 1) {
                return $uploadResult;
            }
            return [
                "status"            => 1,
                "message"           => __("Your video is being uploaded to TikTok. It may take a few minutes for your content to process and be visible on your profile."),
                "tiktok_processing" => true,
                "id"                => $uploadResult['id'],
                "url"               => $uploadResult['url'],
                "type"              => "media",
            ];
        } catch (\Exception $e) {
            return [
                "status"  => 0,
                "message" => __("TikTok error: ") . $e->getMessage(),
                "type"    => $post->type,
            ];
        }
    }

    /**
     * Calculate video_cover_timestamp_ms from the selected frame index.
     *
     * UploadFileService generates 10 frames using:
     *   seekTime[i] = (duration / 11) * (i + 1)   for i in 0..9
     *
     * We reverse this to get the millisecond position.
     * If index is -1 (media library pick) or no selection, use 1000ms (1s default).
     *
     * @param  string|null $mediaPath  path/id_secure of the video file
     * @param  int         $thumbIndex 0–9 for generated frames, -1 for library pick
     * @return int         milliseconds
     */
    protected static function _resolveCoverTimestampMs($mediaPath, int $thumbIndex): int
    {
        // No thumbnail selected, or picked from media library — use TikTok default
        if ($thumbIndex < 0 || $mediaPath === null) {
            return 1000;
        }

        // Try to get actual video duration for the best accuracy
        $duration = 0;
        try {
            $localPath = self::urlToLocalPath(self::resolveMediaUrl((string)$mediaPath));
            if ($localPath && file_exists($localPath)) {
                $getID3   = new getID3;
                $fileInfo = $getID3->analyze($localPath);
                $duration = (float) ($fileInfo['playtime_seconds'] ?? 0);
            }
        } catch (\Throwable $e) {
            // Fall through to default duration
        }

        // If we can't determine duration, assume 60s (safe default for short videos)
        if ($duration <= 0) {
            $duration = 60;
        }

        // Mirror the UploadFileService formula: seekTime = (duration / 11) * (index + 1)
        $numThumbs = 10;
        $seekSecs  = ($duration / ($numThumbs + 1)) * ($thumbIndex + 1);

        // Clamp: must be at least 500ms and at most (duration - 500ms)
        $ms = (int) round($seekSecs * 1000);
        $ms = max(500, min($ms, (int) (($duration * 1000) - 500)));

        return $ms;
    }

    /**
     * Upload video to TikTok via the gimucco library.
     *
     * @param  string     $videoPath   public URL of the video
     * @param  string     $caption
     * @param  array|null $settings    TikTok privacy/comment settings from composer
     * @param  int        $coverMs     video_cover_timestamp_ms (milliseconds)
     */
    protected static function uploadVideo($videoPath, $caption, $settings = [], $coverMs = 1000)
    {
        try {
            if (!empty($settings)) {
                $privacyMap = [
                    'PUBLIC_TO_EVERYONE'    => Connector::PRIVACY_PUBLIC,
                    'MUTUAL_FOLLOW_FRIENDS' => Connector::PRIVACY_FRIENDS,
                    'SELF_ONLY'             => Connector::PRIVACY_PRIVATE,
                ];
                $privacy      = $privacyMap[$settings['privacy_level']] ?? Connector::PRIVACY_PUBLIC;
                $comments_off = $settings['disable_comment'] ?? false;
                $duet_off     = $settings['disable_duet']    ?? false;
                $stitch_off   = $settings['disable_stitch']  ?? false;
            } else {
                if (!get_option("tiktok_mode", 0)) {
                    $privacy      = Connector::PRIVACY_PRIVATE;
                    $comments_off = true;
                    $duet_off     = true;
                    $stitch_off   = true;
                } else {
                    $privacy      = Connector::PRIVACY_PUBLIC;
                    $comments_off = false;
                    $duet_off     = false;
                    $stitch_off   = false;
                }
            }

            // ─── Pass $coverMs as video_cover_timestamp_ms ────────────────────
            // VideoFromUrl constructor signature:
            // __construct($url, $title, $privacy, $comments_off, $duet_off,
            //             $stitch_off, $video_cover_timestamp_ms, ...)
            $video = new VideoFromUrl(
                $videoPath,
                $caption,
                $privacy,
                $comments_off,
                $duet_off,
                $stitch_off,
                $coverMs          // ← custom cover timestamp in milliseconds
            );
            // ──────────────────────────────────────────────────────────────────

            $publishInfo = $video->publish(self::$tiktok);

            if (!$publishInfo || !$publishInfo->getPublishID()) {
                return [
                    "status"  => 0,
                    "message" => "Failed to start video upload: " .
                        (method_exists($publishInfo, 'getErrorMessage')
                            ? $publishInfo->getErrorCode() . " - " . $publishInfo->getErrorMessage()
                            : 'Unknown error'),
                ];
            }

            $publishStatus = self::$tiktok->waitUntilPublished($publishInfo->getPublishID());

            if ($publishStatus->getStatus() == PublishStatus::PUBLISH_COMPLETE) {
                return [
                    "status" => 1,
                    "id"     => $publishStatus->getPublicPostID(),
                    "url"    => $publishStatus->getPublicPostID()
                        ? "https://www.tiktok.com/@video/video/" . $publishStatus->getPublicPostID()
                        : "https://www.tiktok.com/",
                ];
            } else {
                return [
                    "status"  => 0,
                    "message" => $publishStatus->getErrorCode() . ": " . $publishStatus->getErrorMessage(),
                ];
            }
        } catch (\Exception $e) {
            return [
                "status"  => 0,
                "message" => $e->getMessage(),
            ];
        }
    }
    // ─────────────────────────────────────────────────────────────────────────
    // Media URL helpers — safe against double-URL bug
    //
    // medias[] can store either a relative path ("files/img.jpg") or a full
    // public URL ("https://pando.../storage/files/img.jpg").
    // Media::url() prepends the storage base to relative paths — calling it on
    // an already-full URL produces a broken double-URL that every external API
    // rejects.  resolveMediaUrl() detects which case we have and acts accordingly.
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return a full public URL for any stored media identifier.
     * Safe to call on both relative paths and already-full URLs.
     */
    protected static function resolveMediaUrl(string $media): string
    {
        $media = trim($media);
        if ($media === '') return '';
        if (filter_var($media, FILTER_VALIDATE_URL)) return $media;
        return \Media::url($media);
    }

    /** True when the media (URL or path) has an image extension. */
    protected static function mediaIsImage(string $media): bool
    {
        $p = parse_url($media, PHP_URL_PATH) ?: $media;
        return (bool) preg_match('/\.(jpe?g|png|gif|webp|bmp|svg|heic|heif|tiff?)$/i', $p);
    }

    /** True when the media (URL or path) has a video extension. */
    protected static function mediaIsVideo(string $media): bool
    {
        $p = parse_url($media, PHP_URL_PATH) ?: $media;
        return (bool) preg_match('/\.(mp4|mov|avi|mkv|webm|flv|wmv|m4v|3gp|ogv|ts|mts|mpeg|mpg)$/i', $p);
    }

    /**
     * Convert a full public URL to a local absolute filesystem path.
     * Handles both /storage/app/public/… and /storage/… URL shapes.
     * Returns null if the file cannot be found locally (caller should fall
     * back to downloading the URL with curl).
     */
    protected static function urlToLocalPath(string $url): ?string
    {
        $urlPath = parse_url($url, PHP_URL_PATH) ?: '';
        $prefixes = ['/storage/app/public/', '/storage/'];
        foreach ($prefixes as $pfx) {
            if (str_starts_with($urlPath, $pfx)) {
                $rel = substr($urlPath, strlen($pfx));
                $candidates = [
                    storage_path('app/public/' . $rel),
                    storage_path('app/' . $rel),
                    public_path('storage/' . $rel),
                    base_path('storage/app/public/' . $rel),
                ];
                foreach ($candidates as $c) {
                    if (file_exists($c) && filesize($c) > 0) return $c;
                }
            }
        }
        return null;
    }

    /**
     * Stream-download a URL to a local temp file using curl.
     * Much safer than file_get_contents() for large videos.
     * Returns the temp file path, or null on failure.
     */
    protected static function downloadToTemp(string $url, string $prefix = 'media_', string $ext = ''): ?string
    {
        $tmp = tempnam(sys_get_temp_dir(), $prefix) . ($ext ? ".$ext" : '');
        $fh  = fopen($tmp, 'wb');
        if (!$fh) return null;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fh,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 600,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT      => 'Mozilla/5.0',
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fh);
        if ($code === 200 && file_exists($tmp) && filesize($tmp) > 0) return $tmp;
        @unlink($tmp);
        return null;
    }

    /**
     * Resolve a media identifier (URL or relative path) to a local file path.
     * For local storage: maps URL → real filesystem path.
     * For S3/Contabo or when local path not found: downloads to temp file.
     * Caller is responsible for deleting temp files (check $wasTempCreated).
     *
     * @param  string $identifier   Full URL or relative path
     * @param  bool   &$wasTempFile Set to true if a temp file was created
     * @return string|null          Absolute local path, or null on failure
     */
    protected static function resolveToLocalFile(string $identifier, bool &$wasTempFile = false): ?string
    {
        $wasTempFile = false;
        $url = self::resolveMediaUrl($identifier);

        // 1. Try to map URL → local storage path (zero-copy, fastest)
        $local = self::urlToLocalPath($url);
        if ($local) return $local;

        // 2. Fall back: download via curl
        $ext  = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'bin';
        $tmp  = self::downloadToTemp($url, 'media_dl_', $ext);
        if ($tmp) { $wasTempFile = true; return $tmp; }

        return null;
    }

    /**
     * Safely access a property chain on advance_options.
     * Guards against PHP 8 TypeError when advance_options is null.
     */
    protected static function getAdvanceOption($data, string $key, $default = '')
    {
        $ao = $data->advance_options ?? null;
        if ($ao === null) return $default;
        if (is_array($ao))  return $ao[$key]  ?? $default;
        if (is_object($ao)) return $ao->$key  ?? $default;
        return $default;
    }


}
