<?php

namespace Modules\AppChannelInstagramProfiles\Facades;

use Illuminate\Support\Facades\Facade;
use JanuSoftware\Facebook\Facebook;
use Media;

class Post extends Facade
{
    private static $fb;

    public static function initFacebook()
    {
        if (!self::$fb) {
            self::$fb = new Facebook([
                'app_id'                => get_option("instagram_app_id", ""),
                'app_secret'            => get_option("instagram_app_secret", ""),
                'default_graph_version' => get_option("instagram_graph_version", "v21.0"),
            ]);
        }
    }

    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    protected static function validator($post)
    {
        $errors = [];
        $data   = json_decode($post->data, false);
        $medias = $data->medias ?? [];

        if (!in_array($post->type, ['media', 'link'])) {
            $errors[] = __("The Instagram API currently supports posts with the 'Photo' type only.");
        } else {
            if (empty($medias) || (!self::mediaIsImage(self::resolveMediaUrl((string)($medias[0] ?? ''))) && !self::mediaIsVideo(self::resolveMediaUrl((string)($medias[0] ?? ''))))) {
                $errors[] = __("The Instagram API currently supports posts with the 'Photo' type only.");
            }
        }

        if (isset($data->options->ig_type)) {
            $postType = $data->options->ig_type;
            switch ($postType) {
                case 'reels':
                    if (empty($medias) || !self::mediaIsVideo(self::resolveMediaUrl((string)($medias[0] ?? '')))) {
                        $errors[] = __("Instagram Reels only supports videos. Please ensure your video is between 3 seconds and 15 minutes long, as images are not accepted.");
                    }
                    break;
            }
        }

        return $errors;
    }

    protected static function post($post)
    {
        self::initFacebook();
        $data      = json_decode($post->data, false);
        $medias    = $data->medias;
        $caption   = spintax($data->caption);
        $post_type = $data->options->ig_type ?? 'media';
        $comment   = $data->options->ig_comment ?? '';
        $endpoint        = "/" . $post->account->pid . "/media_publish";
        $upload_endpoint = "/" . $post->account->pid . "/media";

        try {
            switch ($post_type) {
                case 'stories':
                case 'reels':
                    return self::handleSingleMediaPost($medias[0], $post_type, $upload_endpoint, $endpoint, $caption, $comment, $post);
                default:
                    return count($medias) === 1
                        ? self::handleSingleMediaPost($medias[0], "media", $upload_endpoint, $endpoint, $caption, $comment, $post)
                        : self::handleCarouselPost($medias, $upload_endpoint, $endpoint, $caption, $comment, $post);
            }
        } catch (\Exception $e) {
            unlink_watermark($medias);
            return [
                "status"  => "error",
                "message" => __($e->getMessage()),
                "type"    => $post->type,
            ];
        }
    }

    protected static function handleSingleMediaPost($media, $media_type, $upload_endpoint, $endpoint, $caption, $comment, $post)
    {
        $media = self::resolveMediaUrl((string)$media);
        switch ($media_type) {
            case 'stories': $media_type = "STORIES"; break;
            case 'reels':   $media_type = "REELS";   break;
            default:        $media_type = self::mediaIsImage($media) ? "IMAGE" : "REELS"; break;
        }

        $upload_params   = self::getMediaUploadParams($media, $caption, $media_type, $post);
        $upload_response = self::$fb->post($upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();
        return self::publishPost($upload_response, $endpoint, $comment, $post, $media_type === "STORIES" ? "stories" : "p");
    }

    protected static function handleCarouselPost($medias, $upload_endpoint, $endpoint, $caption, $comment, $post)
    {
        $media_ids = [];
        foreach ($medias as $media) {
            // Carousel items do not support custom thumbnails — ignore $customThumb here
            $upload_params   = self::getMediaUploadParams(self::resolveMediaUrl((string)$media), $caption, self::mediaIsImage(self::resolveMediaUrl((string)$media)) ? "IMAGE" : "VIDEO", $post, true);
            $upload_response = self::$fb->post($upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();
            $media_ids[]     = $upload_response['id'];
        }

        $upload_params = [
            'media_type' => 'CAROUSEL',
            'children'   => $media_ids,
            'caption'    => $caption,
        ];
        $upload_response = self::$fb->post($upload_endpoint, $upload_params, $post->account->token)->getDecodedBody();
        return self::publishPost($upload_response, $endpoint, $comment, $post, "p");
    }

    protected static function getMediaUploadParams($media, $caption, $media_type, $post, $is_carousel_item = false)
    {
        // $media is already resolved to full URL by the caller
        if (!self::mediaIsImage($media) && !self::mediaIsVideo($media)) {
            throw new \Exception(__("Currently, Instagram only supports posting with videos or images."));
        }

        if (self::mediaIsImage($media)) {
            $params = [
                'media_type' => $media_type,
                'image_url'  => self::resolveMediaUrl((string)watermark($media, $post->account->team_id, $post->account->id)),
                'caption'    => $caption,
            ];
        } else {
            $params = [
                'media_type' => $media_type,
                'video_url'  => $media,  // already a full URL from resolveMediaUrl()
                'caption'    => $caption,
            ];

            // ─── ADD custom thumbnail for video / reels ───────────────────────
            $data        = json_decode($post->data, false);
            $customThumb = $data->custom_thumbnail ?? null;
            if ($customThumb && !$is_carousel_item) {
                $params['cover_url'] = self::resolveMediaUrl((string)$customThumb); // must be full public URL
            }
            // ──────────────────────────────────────────────────────────────────
        }

        if ($is_carousel_item) {
            $params['is_carousel_item'] = true;
        }

        return $params;
    }

    protected static function publishPost($upload_response, $endpoint, $comment, $post, $url_type)
    {
        $attempts = 0;
        do {
            $attempts++;
            sleep(2);
            try {
                $params   = ['creation_id' => $upload_response['id']];
                $response = self::$fb->post($endpoint, $params, $post->account->token)->getDecodedBody();

                if (isset($response["id"])) {
                    self::postComment($response["id"], $comment, $post);
                    $media_response = self::$fb->get("/" . $response["id"] . "?fields=shortcode", $post->account->token)->getDecodedBody();
                    return [
                        "status"  => 1,
                        "message" => __('Succesed'),
                        "id"      => $response["id"],
                        "url"     => "https://www.instagram.com/{$url_type}/" . $media_response['shortcode'],
                        "type"    => $post->type,
                    ];
                }
            } catch (\Exception $e) {
                if ($attempts >= 30) throw $e;
            }
        } while ($attempts <= 30);

        return [
            "status"  => 0,
            "message" => __('The media is not ready for publishing, please wait for a moment'),
        ];
    }

    protected static function postComment($post_id, $comment, $post)
    {
        if ($comment) {
            try {
                self::$fb->post("/" . $post_id . "/comments", [
                    "message" => $comment,
                ], $post->account->token)->getDecodedBody();
            } catch (\Exception $e) {
                // Silently ignore
            }
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
