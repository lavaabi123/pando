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
            $media = Media::url($medias[0]);
            if (!Media::isVideo($media)) {
                $errors[] = __("TikTok only supports video uploads.");
            } else {
                $getID3   = new getID3;
                $fileInfo = $getID3->analyze(Media::path($medias[0]));
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
        $videoPath      = Media::url($medias[0] ?? '');
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
            $localPath = Media::path($mediaPath);
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
}
