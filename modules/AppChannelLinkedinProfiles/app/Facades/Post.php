<?php
namespace Modules\AppChannelLinkedinProfiles\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppChannelLinkedinProfiles\Classes\LinkedinAPI;
use Modules\AppChannels\Models\Accounts;
use Media;

class Post extends Facade
{
    private static $linkedin;

    /**
     * Initializes the LinkedinAPI object and optionally loads the token.
     *
     * @param string|null $token The LinkedIn access token.
     */
    public static function initLinkedin($token = null)
    {
        if (!self::$linkedin) {
            // Initialize LinkedinAPI with app settings (replace get_option() with your config mechanism)
            self::$linkedin = new LinkedinAPI(
                get_option("linkedin_app_id", ""),
                get_option("linkedin_app_secret", ""),
                "",
                "",
                false
            );
        }
        // Note: The LinkedinAPI class does not include a setAccessToken() method.
        // We'll pass the access token into each method call.
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

        // Validate allowed post types for LinkedIn.
        if (!in_array($post->type, ['media', 'link', 'text'])) {
            $errors[] = __("LinkedIn API currently supports only 'media', 'link', or 'text' post types.");
        }

        // Validate advanced options if provided.
        if (isset($data['advance_options']['linkedin_post_type'])) {
            $postType = $data['advance_options']['linkedin_post_type'];
            switch ($postType) {
                case 'video':
                    if (!empty($medias) && self::mediaIsImage(self::resolveMediaUrl((string)($medias[0] ?? '')))) {
                        $errors[] = __("LinkedIn requires a video for the 'video' post type; images are not supported.");
                    }
                    break;
                default:
                    break;
            }
        }

        return $errors;
    }

    /**
     * Publishes a post to LinkedIn using the LinkedinAPI.
     *
     * Supported types:
     * - text: text-only post.
     * - link: post text with an appended link.
     * - media: post one or more images.
     *
     * Note: Video posts are not supported in this example.
     *
     * @param object $post The post object.
     * @return array The standardized response from the LinkedIn posting.
     */
    protected static function post($post)
    {
        $accessToken = $post->account->token;

        // Initialize LinkedinAPI.
        self::initLinkedin($accessToken);
        $linkedin = self::$linkedin;

        // Retrieve the person ID from LinkedIn.
        $person_id = $linkedin->getPersonID($accessToken);

        $data    = json_decode($post->data, false);

        $medias  = $data->medias ?? [];
        $caption = spintax($data->caption);
        // Optionally, get a first comment from advanced options.
        // Guard: advance_options can be null when no advanced settings were saved
        $comment    = self::getAdvanceOption($data, 'linkedin_first_comment', '');
        $comment = empty(trim($comment)) ? $data->options->linkedin_first_comment : '';
        // For link posts.
        $link       = $data->link ?? '';
        $link_title = self::getAdvanceOption($data, 'link_title', '');
        $link_desc  = self::getAdvanceOption($data, 'link_description', '');
        $visibility = "PUBLIC";  // Default visibility.
        // Thumbnail: prefer linkedin_thumbnail, fall back to custom_thumbnail
        $_thumbRaw = $data->linkedin_thumbnail ?? $data->custom_thumbnail ?? null;
        $custom_thumbnail = !empty($_thumbRaw) ? self::resolveMediaUrl((string)$_thumbRaw) : null;


        // Posting logic based on the post type.
        switch ($post->type) {
            case 'text':
                // Text-only post.
                $response = $linkedin->linkedInTextPost($accessToken, $person_id, $caption, $visibility, $comment);
                break;

            case 'link':
                // Post with a link.
                $response = $linkedin->linkedInLinkPost($accessToken, $person_id, $caption, $link_title, $link_desc, $link, $visibility, $comment);
                break;

            case 'media':
                if (count($medias) > 1) {
                    // Multi-media post (multiple images).
                    $images = [];
                    foreach ($medias as $media) {
                        $img_arr['image_path'] = self::resolveMediaUrl((string)watermark(self::resolveMediaUrl((string)$media), $post->account->team_id, $post->account->id));
                        $img_arr['desc']       = $caption;
                        // You can customize the title; here we use a substring of the caption.
                        $img_arr['title']      = substr($caption, 0, 200);
                        $images[] = $img_arr;
                    }
                    
                    $response = $linkedin->linkedInMultiplePhotosPost(
                        $accessToken,
                        $person_id,
                        $caption,
                        $images,
                        $visibility,
                        $comment 
                    );
                } else {
                    // Single media post (image or video).
                    $media = self::resolveMediaUrl((string)($medias[0] ?? ''));
                    if (!$media) {
                        return self::errorResponse(__("No media provided for single media post."), $post->type);
                    }
                    if (self::mediaIsVideo($media)) {
                        //return self::errorResponse(__("LinkedIn video posts are not supported."), $post->type);
                        $response   = $linkedin->linkedInVideoPost($accessToken, $person_id, $caption, $media, substr($caption, 0, 200), substr($caption, 0, 200), $visibility, $custom_thumbnail, $comment);
                    } elseif (self::mediaIsImage($media)) {
                        // For a single image, apply watermark and post.
                        $image_path = self::resolveMediaUrl((string)watermark($media, $post->account->team_id, $post->account->id));
                        $response   = $linkedin->linkedInPhotoPost($accessToken, $person_id, $caption, $image_path, substr($caption, 0, 200), substr($caption, 0, 200), $visibility, $comment);
                    } else {
                        return self::errorResponse(__("Unsupported media type."), $post->type);
                    }
                }
                break;

            default:
                return self::errorResponse(__("Unsupported post type"), $post->type);
        }

        $responseObj = json_decode($response);
        if (isset($responseObj->message)) {
            return self::errorResponse(__($responseObj->message), $post->type);
        }

        return [
            "status"  => 1,
            "message" => __('Succeeded'),
            "id"      => $responseObj->id ?? '',
            "url"     => "https://www.linkedin.com/feed/update/" . ($responseObj->id ?? ''),
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