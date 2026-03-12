<?php
namespace Modules\AppChannelPinterestBoards\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppChannelPinterestBoards\Classes\PinterestAPI;
use Modules\AppChannels\Models\Accounts;
use Media;

class Post extends Facade
{
    private static $pinterest;

    /**
     * Initializes the PinterestAPI object and optionally loads the token.
     *
     * @param string|null $token The Pinterest access token.
     */
    public static function initPinterest()
    {
        if (!self::$pinterest) {
            // Initialize PinterestAPI with app settings (replace get_option() with your config mechanism)
            self::$pinterest = new PinterestAPI(
                get_option("pinterest_client_id", ""),
                get_option("pinterest_client_secret", ""),
                "",
            );
        }
    }

    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    /**
     * Validates the post object for Pinterest posting.
     *
     * Pinterest requires at least one image.
     *
     * @param object $post The post data object.
     * @return array A list of error messages.
     */
    protected static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, false);
        $medias  = $data->medias ?? [];
        $options = $data->options;

        if (empty($medias)) {
            $errors[] = __("At least one media or video is required to create a pin.");
        } else {
            // Ensure that the provided media is an image.
            $media = self::resolveMediaUrl((string)($medias[0] ?? ''));
            if (!self::mediaIsImage($media) && !self::mediaIsVideo($media)) {
                $errors[] = __("The provided media must be an image or video for Pinterest pins.");
            }
        }

        if(isset($options->pinterest_link) && $options->pinterest_link != ""){
            $url = $options->pinterest_link;
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = __( "Pinterest: Link is not a valid URL");
            }
        }

        return $errors;
    }

    /**
     * Shares a pin to Pinterest using the PinterestAPI.
     *
     * Supported types:
     * - media: pin with an image.
     * - link: pin with an image and a link attached.
     *
     * The board ID is assumed to be stored in $post->account->pid and the access
     * token in $post->account->token.
     *
     * @param object $post The post object.
     * @return array The standardized response from the Pinterest posting.
     */
    protected static function post($post)
    {
        self::initPinterest();
        $accessToken = json_decode($post->account->token);
        $renewToken = self::$pinterest->getRefreshTokenAccessToken( $accessToken->refresh_token, str_replace(" ", ",", $accessToken->scope) );
        if( isset($renewToken['message']) ){
            Accounts::where("id", $post->account->id)->update(["status" => 0]);
            return self::errorResponse( __("Access token expired") , $post->type);
        }

        self::$pinterest->setAccessToken($renewToken['access_token']);
        $accessToken = $renewToken['access_token'];
        $pinterest = self::$pinterest;

        // Here, we assume that the board ID (to which the pin will be posted) is stored in $post->account->pid.
        $boardId = $post->account->pid;

        $data    = json_decode($post->data, false);
        $medias  = $data->medias ?? [];
        $title =  spintax($data->options->pinterest_title ?? '');
        $pinterest_link =  $data->options->pinterest_link ?? '';
        $caption = spintax($data->caption);
        // For link posts, we get an optional link to attach
        $link = $data->link ?? '';

        if($pinterest_link != ""){
            $link = $pinterest_link;   
        }

        // Thumbnail for video pins.
        // New composer stores it as 'custom_thumbnail' (same key Facebook uses).
        // Fall back to 'pinterest_thumbnail' for any legacy saved posts.
        $coverImageUrl = null;
        if (!empty($data->custom_thumbnail)) {
            $coverImageUrl = self::resolveMediaUrl((string)$data->custom_thumbnail);
        } elseif (!empty($data->pinterest_thumbnail)) {
            $coverImageUrl = self::resolveMediaUrl((string)$data->pinterest_thumbnail);
        }

        // Call the sharePin method.
        $response = $pinterest->sharePin($accessToken, $boardId, $title, $caption, $link, $medias, $coverImageUrl);

        // The Pinterest API should return a result. If an error is detected, process it.
        if (isset($response['message'])) {
            return self::errorResponse(__($response['message']), $post->type);
        }
        if (!isset($response['id'])) {
            return self::errorResponse(__("Unknown error occurred while sharing pin."), $post->type);
        }

        return [
            "status"  => 1,
            "message" => __('Succeeded'),
            "id"      => $response['id'],
            // Pinterest pin URL pattern – adjust if different.
            "url"     => "https://www.pinterest.com/pin/" . $response['id'],
            "type"    => $post->type,
        ];
    }

    /**
     * Returns a standardized error response.
     *
     * @param string $message The error message.
     * @param string $type    The post type.
     * @return array The error response.
     */
    protected static function errorResponse($message, $type)
    {
        return [
            "status"  => 0,
            "message" => __($message),
            "type"    => $type,
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