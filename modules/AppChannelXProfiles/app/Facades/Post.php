<?php
namespace Modules\AppChannelXProfiles\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppChannelXProfiles\Classes\XApi;
use Modules\AppChannels\Models\Accounts;
use Media;

class Post extends Facade
{
    private static $xapi;

    /**
     * Initializes the XApi object and sets its token using the provided token value.
     *
     * @param string $token The access token from $post->account->token.
     */
    public static function initXApi($token = null)
    {
        if (!self::$xapi) {
            self::$xapi = new XApi(
                get_option("x_client_id", ""),
                get_option("x_client_secret", "")
            );
        }
        if ($token) {
            self::$xapi->setAccessToken($token);
        }
    }

    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    /**
     * Validates the post object.
     *
     * @param object $post The post data object.
     * @return array A list of error messages.
     */
    protected static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, true);
        $medias = $data['medias'] ?? [];

        // Validate allowed post types for X. Supported types: media, link, and text.
        if (!in_array($post->type, ['media', 'link', 'text'])) {
            $errors[] = __("The X API currently supports only posts of type 'media', 'link' or 'text'.");
        }

        // Validate advanced options if provided.
        if (isset($data['advance_options']['x_post_type'])) {
            $postType = $data['advance_options']['x_post_type'];
            switch ($postType) {
                case 'video':
                    if (!empty($medias) && self::mediaIsImage(self::resolveMediaUrl((string)($medias[0] ?? '')))) {
                        $errors[] = __("X API requires a video for the 'video' post type; images are not supported.");
                    }
                    break;
                // Additional validation cases can be added as needed.
                default:
                    break;
            }
        }

        return $errors;
    }

    /**
     * Publishes a post to X using the XApi.
     *
     * This method refreshes the access token before posting.
     * 
     * Supported types:
     * - text: post text only.
     * - link: post text with an appended link.
     * - video: post a single video.
     * - media: post one or more images.
     *
     * @param object $post The post object.
     * @return array The response from the X API.
     */
    protected static function post($post)
    {
        // Refresh the access token before proceeding.
        $tokenInfo = json_decode($post->account->token);

        self::initXApi($tokenInfo->access_token);
        if (isset($tokenInfo->refresh_token) && !empty($tokenInfo->refresh_token)) {
            $refreshed = self::$xapi->refreshToken($tokenInfo->refresh_token);

            // refreshToken() returns array on failure, object on success
            if (is_array($refreshed)) {
                Accounts::where("id", $post->account->id)->update(["status" => 0]);
                return [
                    "status"  => "error",
                    "message" => __($refreshed["message"] ?? "Token refresh failed"),
                    "type"    => $post->type,
                ];
            }

            // Guard: no access_token in refreshed object
            if (!isset($refreshed->access_token) || empty($refreshed->access_token)) {
                Accounts::where("id", $post->account->id)->update(["status" => 0]);
                return [
                    "status"  => "error",
                    "message" => __("X token refresh returned no access token. Please reconnect your X account."),
                    "type"    => $post->type,
                ];
            }

            self::initXApi($refreshed->access_token);
            Accounts::where("id", $post->account->id)->update(["token" => json_encode($refreshed)]);
        }

        $data = json_decode($post->data, false);
        $medias = $data->medias ?? [];
        $caption = spintax($data->caption);
        $comment = self::getAdvanceOption($data, 'x_first_comment', '');


        // Posting logic based on post type.
        switch ($post->type) {
            case 'text':
                // Text-only post.
                $response = self::$xapi->postTweet($caption, []);
                break;

            case 'link':
                // Append link if available.
                $link = $data->link ?? '';
                if ($link) {
                    $caption .= " " . $link;
                }
                $response = self::$xapi->postTweet($caption, []);
                break;

            case 'media':
                if (count($medias) > 1) {
                    // Multi-image (or multi-media) post.
                    $response = self::handleMultiMediaPost($medias, $caption, $comment, $post);
                } else {
                    // Single media post (image or video).
                    $response = self::handleSingleMediaPost($medias[0] ?? null, $post->type, $caption, $comment, $post);
                }
                break;

            default:
                // Unknown post type, return error.
                return [
                    "status"  => "error",
                    "message" => __("Unsupported post type"),
                    "type"    => $post->type,
                ];
        }

        // If handleSingleMediaPost / handleMultiMediaPost returned an error array, pass it through
        if (is_array($response)) {
            return $response;
        }

        // Null response = curl failed entirely (network/SSL issue or empty body)
        if ($response === null) {
            return self::errorResponse(__('X API returned no response. Check server connectivity and SSL settings.'), $post->type);
        }

        // X returned an HTTP-level error object: { "title": "...", "detail": "...", "status": 4xx }
        if (isset($response->status) && $response->status >= 400) {
            $errMsg = $response->detail ?? $response->title ?? ('X API error ' . $response->status);
            return self::errorResponse($errMsg, $post->type);
        }

        // X returned errors array: { "errors": [{ "message": "..." }] }
        if (isset($response->errors) && !isset($response->data)) {
            $errMsg = $response->errors[0]->message ?? $response->errors[0]->detail ?? 'X API error';
            return self::errorResponse($errMsg, $post->type);
        }

        // No data->id means the tweet was not created
        if (!isset($response->data->id)) {
            $errMsg = $response->detail
                ?? (isset($response->errors[0]) ? ($response->errors[0]->message ?? $response->errors[0]->detail ?? 'Unknown error') : null)
                ?? json_encode($response);
            return self::errorResponse($errMsg, $post->type);
        }

        return [
            "status"  => 1,
            "message" => __('Succeeded'),
            "id"      => $response->data->id,
            "url"     => "https://x.com/" . $response->data->id,
            "type"    => $post->type,
        ];
    }

    /**
     * Handles posting a single-media post.
     *
     * @param string $media      Media URL or file path.
     * @param string $mediaType  The type of media (video or media).
     * @param string $caption    The caption for the post.
     * @param string $comment    The first comment to post as a reply.
     * @param object $post       The post object.
     * @return array Response from X API or error structure if an error occurred.
     */
    protected static function handleSingleMediaPost($media, $mediaType, $caption, $comment, $post)
    {
        // Pass local storage path first so XApi can skip HTTP download.
        // Media::localPath() returns the absolute local path if available.
        // Fall back to Media::url() so XApi can curl-download it.
        // resolveMediaUrl() returns full public URL safely (no double-URL).
        // XApi::uploadMedia() then maps URL → local path via urlToLocalPath(),
        // falling back to curl download — no Media:: calls needed here.
        $mediaInput = self::resolveMediaUrl((string)($media ?? ''));

        $mediaId = self::$xapi->uploadMedia($mediaInput);

        if (!$mediaId) {
            return self::errorResponse(
                __('Media upload to X failed. Ensure the video is MP4 (H.264/AAC), under 512 MB, and under 140 seconds.'),
                $post->type
            );
        }

        $response = self::$xapi->postTweet($caption, [$mediaId]);

        if ($comment && isset($response->data->id)) {
            self::postComment($response->data->id, $comment, $post);
        }

        return $response;
    }

    /**
     * Handles posting a multi-media (multiple images) post.
     *
     * @param array  $medias  Array of media URLs or file paths.
     * @param string $caption The caption for the post.
     * @param string $comment The first comment to post as a reply.
     * @param object $post    The post object.
     * @return array Response from X API or error structure if an error occurred.
     */
    protected static function handleMultiMediaPost($medias, $caption, $comment, $post)
    {
        // X API hard limit: maximum 4 media_ids per tweet.
        $medias = array_slice((array)$medias, 0, 4);

        $mediaIds = [];
        foreach ($medias as $media) {
            $mediaInput = self::resolveMediaUrl((string)$media);
            $mediaId = self::$xapi->uploadMedia($mediaInput);
            if ($mediaId) {
                $mediaIds[] = $mediaId;
            }
        }

        // If no media uploaded at all, return an error — do NOT post caption-only.
        if (empty($mediaIds)) {
            return self::errorResponse(__('All media uploads to X failed. Ensure files are supported formats under 512 MB.'), $post->type);
        }

        $response = self::$xapi->postTweet($caption, $mediaIds);
        
        if ($comment && isset($response->data->id)) {
            self::postComment($response->data->id, $comment, $post);
        }
        
        return $response;
    }

    /**
     * Posts a comment/reply associated with a published post.
     *
     * @param string $postId The ID of the published post.
     * @param string $comment The comment message.
     * @param object $post    The post object.
     */
    protected static function postComment($postId, $comment, $post)
    {
        if ($comment) {
            try {
                // Uncomment and adjust the line below if the X API supports posting comments/replies.
                // self::$xapi->postComment($postId, $comment);
            } catch (\Exception $e) {
                // Silently ignore comment errors.
            }
        }
    }

    // Return an error response
    private static function errorResponse($message, $type)
    {
        return [
            "status"  => 0,
            "message" => __($message),
            "type"    => $type
        ];
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