<?php
namespace Modules\AppChannelYouTubeChannels\Facades;

use Illuminate\Support\Facades\Facade;
use Google\Client;
use Google\Service\YouTube;
use Modules\AppChannels\Models\Accounts;
use Media;

class Post extends Facade
{
    protected static $client;

    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }


    /**
     * Initialize and retrieve the YouTube API instance.
     *
     * @return YouTube
     */
    protected static function initYouTube()
    {
        self::$client = new Client();
        self::$client->setClientId(get_option("youtube_client_id", ""));
        self::$client->setClientSecret(get_option("youtube_client_secret", ""));
        self::$client->setDeveloperKey(get_option("youtube_api_key", ""));
        self::$client->setApplicationName("Youtube");
        self::$client->setApprovalPrompt("force");
        self::$client->setAccessType('offline');
        self::$client->setScopes(
            [
                'https://www.googleapis.com/auth/youtube', 
                'https://www.googleapis.com/auth/userinfo.email'
            ]
        );

        return self::$client;
    }

    /**
     * Validate post data.
     *
     * @param object $post
     * @return array Array of errors if any.
     */
    protected static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, false);
        $medias = $data->medias;
        $options = $data->options;

        // Validate video path and check if it's a valid video
        if (!empty($medias)) {
            $media = self::resolveMediaUrl((string)($medias[0] ?? ''));
            if (!self::mediaIsVideo($media)) {
                $errors[] = __( "YouTube only supports video uploads. Please provide a valid video file.");
            }
        }

        if(!isset($options->youtube_type) || $options->youtube_type == "short"){
            $getID3 = new \getID3;
            $_ytValPath = self::urlToLocalPath(self::resolveMediaUrl((string)($medias[0] ?? '')));
            $fileInfo = ($_ytValPath && file_exists($_ytValPath)) ? $getID3->analyze($_ytValPath) : [];
            if(isset($fileInfo['video']) && isset($fileInfo['playtime_seconds'])){
                $resolution_x = $fileInfo['video']['resolution_x'];
                $resolution_y = $fileInfo['video']['resolution_y'];
                $playtime_seconds = $fileInfo['playtime_seconds'];
                $resolution = $resolution_x/$resolution_y;

                if($resolution < 0.5 || $resolution > 1 || $playtime_seconds > 180){
                    $errors[] = __("YouTube: The video resolution is not suitable for Shorts, or the video exceeds the 3-minute limit.");
                }
            }
        }

        if(!isset($options->youtube_title) || $options->youtube_title == ""){
            $errors[] = __("Youtube: The video must have a title.");
        }

        if(!isset($options->youtube_category) || (int)$options->youtube_category == 0){
            $errors[] = __("Youtube: Please choose a category for your video.");
        }

        return $errors;
    }

    /**
     * Main method to upload a video to YouTube.
     *
     * @param object $post
     * @return array Upload response.
     */
    protected static function post($post)
    {
        self::initYouTube();
        $tokenInfo = json_decode($post->account->token, false);
        self::$client->setAccessToken($post->account->token);
        if (isset($tokenInfo->refresh_token) && !empty($tokenInfo->refresh_token)) {
            $refreshed = self::$client->fetchAccessTokenWithRefreshToken($tokenInfo->refresh_token);
            if (is_array($refreshed) && isset($refreshed["error"])) {

                Accounts::where("id", $post->account->id)->update(["status" => 0]);
                return [
                    "status"  => "error",
                    "message" => __("Access Token Expired"),
                    "type"    => $post->type,
                ];
            }

            Accounts::where("id", $post->account->id)->update(["token" => json_encode($refreshed)]);
        }

        // Decode the JSON data from the post.
        $data = json_decode($post->data, false);
        $options = $data->options;
        $medias = $data->medias ?? [];
        $title = spintax($options->youtube_title);
        $description = spintax($data->caption);
        $categoryId = $options->youtube_category;
        $thumbnail = $options->youtube_thumbnail ?? null;
		if (empty($thumbnail)) {
			$thumbnail = $data->custom_thumbnail ?? null;
		}
        $tags = $options->youtube_tags ?? "";
        $privary_status = false;

        //Check is Short Video
        if(!isset($options->youtube_type) || $options->youtube_type == "short"){
            $getID3 = new \getID3;
            $_ytPath = self::urlToLocalPath(self::resolveMediaUrl((string)($medias[0] ?? '')));
            $fileInfo = ($_ytPath && file_exists($_ytPath)) ? $getID3->analyze($_ytPath) : [];
            if(isset($fileInfo['video']) && isset($fileInfo['playtime_seconds'])){
                $resolution_x = $fileInfo['video']['resolution_x'];
                $resolution_y = $fileInfo['video']['resolution_y'];
                $playtime_seconds = $fileInfo['playtime_seconds'];
                $resolution = $resolution_x/$resolution_y;

                if($resolution < 0.5 || $resolution > 1 || $playtime_seconds > 180){
                    $errors[] = __("YouTube: The video resolution is not suitable for Shorts, or the video exceeds the 3-minute limit.");
                    return [
                        "status"  => "error",
                        "message" => __("YouTube: The video resolution is not suitable for Shorts, or the video exceeds the 3-minute limit."),
                        "type"    => $post->type,
                    ];
                }
            }
        }

        // Upload the video.
        $videoPath = self::resolveMediaUrl((string)($medias[0] ?? ''));
        if (!$videoPath) {
            return self::errorResponse(__("No media provided for single media post."), $post->type);
        }

        if (!self::mediaIsVideo($videoPath)) {
            return self::errorResponse(__("Unsupported post type"), $post->type);
        }

        try {
            return self::handleVideoUpload(
                $videoPath,
                $title,
                $description,
                $categoryId,
                $privary_status,
                $tags,
                $thumbnail
            );
        } catch (Google\Service\Exception $e) {
            $errors = $e->getErrors();
            if(!empty($errors) && isset($errors[0]["message"])){
                return [
                    "status"  => "error",
                    "message" => __($errors[0]["message"])
                ];
            }else{
                return [
                    "status"  => "error",
                    "message" => $e->getMessage()
                ];
            }
        } catch (Google\Exception $e) {
            $errors = $e->getErrors();
            if(!empty($errors) && isset($errors[0]["message"])){
                return [
                    "status"  => "error",
                    "message" => __($errors[0]["message"])
                ];
            }else{
                return [
                    "status"  => "error",
                    "message" => $e->getMessage()
                ];
            }
        } catch (\Exception $e) {
            return [
                "status"  => "error",
                "message" => $e->getMessage()
            ];
        }
    }

    /**
     * Upload the video to YouTube.
     *
     * @param string $videoPath
     * @param string $title
     * @param string $description
     * @param string $categoryId
     * @param bool   $privary_status
     * @param array  $tags
     * @return array Upload response.
     */
    protected static function handleVideoUpload($videoPath, $title, $description, $categoryId, $privary_status, $tags, $thumbnailUrl)
    {
        $youtube = new YouTube(self::$client);

        // Prepare video snippet
        $videoSnippet = new YouTube\VideoSnippet();
        $videoSnippet->setTitle($title);
        $videoSnippet->setDescription($description);
        $videoSnippet->setCategoryId($categoryId);

        if (!empty($tags)) {
            $videoSnippet->setTags($tags);
        }

        // Prepare video status
        $videoStatus = new YouTube\VideoStatus();
        $videoStatus->setPrivacyStatus('public');
        if ($privary_status) {
            $videoStatus->setPrivacyStatus('unlisted'); 
        }

        // Combine snippet and status
        $video = new YouTube\Video();
        $video->setSnippet($videoSnippet);
        $video->setStatus($videoStatus);

        // Upload video to YouTube
        // Use curl stream-download to avoid exhausting memory on large files.
        // file_get_contents() on a URL loads the entire video into RAM.
        $_ytTmpCreated = false;
        $_ytLocalFile  = self::urlToLocalPath($videoPath);
        if (!$_ytLocalFile) {
            // Not resolvable locally — download to temp file
            $_ytExt       = strtolower(pathinfo(parse_url($videoPath, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'mp4';
            $_ytLocalFile = self::downloadToTemp($videoPath, 'yt_vid_', $_ytExt);
            $_ytTmpCreated = (bool)$_ytLocalFile;
        }
        if (!$_ytLocalFile) {
            return self::errorResponse(__("Could not read video file for upload."), "media");
        }
        $response = $youtube->videos->insert(
            'snippet,status',
            $video,
            [
                'data'       => file_get_contents($_ytLocalFile),
                'mimeType'   => 'video/*',
                'uploadType' => 'multipart',
            ]
        );
        if ($_ytTmpCreated) @unlink($_ytLocalFile);


        if($response->getStatus()->uploadStatus != "uploaded"){
            return self::errorResponse(__("Video upload unsuccessful. Please ensure that the video meets YouTube's upload requirements, including format, size, and duration."), "media");
        }

        ///////////////////////////////////////////////
        // Step 2: Set the thumbnail using a thumbnail URL
        ///////////////////////////////////////////////
        if ($thumbnailUrl) {
            $contextOptions = [
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ];
            $context = stream_context_create($contextOptions);
            $thumbnailContent = @file_get_contents($thumbnailUrl, false, $context);
            if($thumbnailContent){
                try {
                    $youtube->thumbnails->set(  $response->getId() , [
                        'data' => $thumbnailContent,
                    ]);
                } catch (\Exception $e) {}
            }
        }

        return [
            "status" => 1,
            "message" => __('Success'),
            "id" => $response->getId(),
            "url" => "https://www.youtube.com/watch?v=".$response->getId(),
            "type" => "media"
        ]; 
    }

    /**
     * Returns a standardized error response.
     *
     * @param string $message The error message.
     * @param string $type    The post type.
     * @return array A standardized error response.
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
