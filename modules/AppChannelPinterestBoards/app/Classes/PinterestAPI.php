<?php

namespace Modules\AppChannelPinterestBoards\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Media;

class PinterestAPI
{
    const BASE_PINTEREST_URL = 'https://www.pinterest.com';
    const BASE_PINTEREST_API_URL = 'https://api.pinterest.com/v5';

    protected $app_id;
    protected $app_secret;
    protected $callback_url;
    protected $graph_version; // hoặc apiVersion nếu bạn muốn đổi tên
    protected $accessToken; 
    protected $mode; 
    protected $client;
    protected $baseApiUrl = 'https://api.pinterest.com/v5';

    /**
     * Constructor.
     *
     * @param string $app_id       The Pinterest app ID.
     * @param string $app_secret   The Pinterest app secret.
     * @param string $callback_url The OAuth callback URL.
     * @param string $graph_version The API version (default: "v1.0").
     */
    public function __construct($app_id, $app_secret, $callback_url, $graph_version = "v1.0")
    {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->callback_url = $callback_url;
        $this->graph_version = $graph_version;
        $this->client = new Client(['verify' => false]);

        if(get_option("pinterest_mode", 0) == 0){
            $this->baseApiUrl = "https://api-sandbox.pinterest.com/v5";
        }
    }

    /**
     * Optionally set an access token for subsequent requests.
     *
     * @param string $token
     */
    public function setMode($mode)
    {
        if($mode == 1){
            $this->baseApiUrl = "https://api.pinterest.com/v5";
        }else{
            $this->baseApiUrl = "https://api-sandbox.pinterest.com/v5";
        }
    }

    /**
     * Optionally set an access token for subsequent requests.
     *
     * @param string $token
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    /**
     * Generates the Pinterest authorization URL.
     *
     * @param string $scopes    A comma-separated list of scopes.
     * @param array  $params    Optional additional parameters.
     * @param string $separator Parameter separator (default: '&').
     * @return string
     */
    public function getAuthorizationUrl($scopes, array $params = [], $separator = '&')
    {
        $params += [
            'client_id' => $this->app_id,
            'redirect_uri' => $this->callback_url,
            'response_type' => 'code',
            'scope' => $scopes,
            'state' => rand_string()
        ];

        return static::BASE_PINTEREST_URL . '/oauth/?' . http_build_query($params, null, $separator);
    }

    /**
     * Retrieves an access token from code.
     *
     * @param string $code
     * @return array
     */
    public function getAccessTokenFromCode($code)
    {
        $endpoint = $this->baseApiUrl . "/oauth/token";
        $params = [
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->callback_url,
            'code' => $code
        ];

        return $this->sendRequest("POST", $endpoint, $params);
    }

    /**
     * Retrieves an access token using a refresh token.
     *
     * @param string $refreshToken
     * @param string $scopes
     * @return array
     */
    public function getRefreshTokenAccessToken($refreshToken, $scopes)
    {
        $endpoint = $this->baseApiUrl . "/oauth/token";
        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'scope' => $scopes,
            'refresh_on' => true
        ];

        return $this->sendRequest("POST", $endpoint, $params);
    }

    /**
     * Wrapper for GET requests.
     *
     * @param string      $endpoint    Endpoint (without base URL).
     * @param array       $params      Optional parameters.
     * @param string|null $accessToken Optional token.
     * @return array
     */
    public function get(string $endpoint, array $params = [], string $accessToken = null)
    {
        $url = $this->baseApiUrl . $endpoint;
        return $this->sendRequest('GET', $url, $params, $accessToken);
    }

    /**
     * Wrapper for POST requests.
     *
     * @param string      $endpoint    Endpoint (without base URL).
     * @param array       $params      Data to send.
     * @param string|null $accessToken Optional token.
     * @return array
     */
    public function post(string $endpoint, array $params = [], string $accessToken = null)
    {
        $url = $this->baseApiUrl . '/' . $this->graph_version . $endpoint;
        return $this->sendRequest('POST', $url, $params, $accessToken);
    }

    /**
     * Shares a pin to Pinterest.
     *
     * This method creates a new Pin on a specified board using the Pinterest API v5.
     *
     * Example payload:
     * {
     *   "board_id": "1234567890",
     *   "note": "This is my pin description",
     *   "link": "https://example.com",
     *   "media_source": {
     *     "source_type": "image_url",
     *     "url": "https://example.com/img.jpg"
     *   }
     * }
     *
     * @param string $accessToken The access token.
     * @param string $boardId     The Pinterest board ID to post the pin on.
     * @param string $note        The pin description.
     * @param string $link        (Optional) A URL to attach with the pin.
     * @param string $imageUrl    The URL of the image to pin.
     * @return array The API response.
     */
    public function sharePin($accessToken, $boardId, $title, $description, $link, $medias, $coverImageUrl = null)
    {
        $endpoint   = $this->baseApiUrl . "/pins";
        $firstMedia = $this->resolveMediaUrl((string)($medias[0] ?? ''));

        if ($this->mediaIsVideo($firstMedia)) {
            return $this->sharePinVideo($accessToken, $boardId, $title, $description, $link, $firstMedia, $coverImageUrl);
        }

        // Pinterest multiple_image_urls: max 5 items.
        // Send ALL images as-is using their original stored URLs — Pinterest fetches
        // them directly from the internet. Do NOT generate padded/temp URLs.
        // Pinterest handles mixed ratios by cropping to the first image's ratio.
        $medias = array_slice((array)$medias, 0, 5);

        $imgItems = [];
        foreach ($medias as $media) {
            $media = $this->resolveMediaUrl((string)$media);
            if (!$this->mediaIsImage($media)) continue;

            $imgItem = ['description' => $description, 'url' => $media];
            if ($title !== '')                                            $imgItem['title'] = $title;
            if ($link !== '' && filter_var($link, FILTER_VALIDATE_URL))  $imgItem['link']  = $link;
            $imgItems[] = $imgItem;
        }

        $countImg = count($imgItems);

        if ($countImg > 2) {
            $params = ['media_source' => ['source_type' => 'multiple_image_urls', 'items' => $imgItems]];
        } else {
            $params = ['media_source' => ['source_type' => 'image_url', 'url' => $firstMedia]];
        }

        $params['board_id'] = $boardId;
        $params['description'] = $description;
        $params['alt_text'] = $description;

        if($title != ""){
            $params['title'] = $title;
        }

        if($link != "" && filter_var($link, FILTER_VALIDATE_URL)){
            $params['link'] = $link;
        }

        return $this->sendRequest("POST", $endpoint, $params, $accessToken);
    }

      /**
     * Post a video pin to Pinterest.
     *
     * This function performs three main steps:
     * 1. Register your intent to upload video media (POST /media).
     * 2. Upload the video file to the provided upload URL.
     * 3. Create the video pin by calling the /pins endpoint.
     *
     * @param string $accessToken The Pinterest access token.
     * @param string $boardId The board ID on which to pin the video.
     * @param string $title The title for the pin.
     * @param string $description The description for the pin.
     * @param string $link A URL to include with the pin (optional).
     * @param string $videoMedia The media identifier (or local identifier) for the video.
     * @return array API response.
     */
    /**
     * Post a video pin to Pinterest using the correct v5 API flow:
     *   1. POST /v5/media           — register upload intent → media_id + upload_url + upload_parameters
     *   2. POST {upload_url}        — upload file bytes to S3 (multipart with upload_parameters)
     *   3. Poll GET /v5/media/{id}  — wait for status = "succeeded"
     *   4. POST /v5/pins            — create pin with media_source.source_type = "video_id"
     */
    public function sharePinVideo($accessToken, $boardId, $title, $description, $link, $videoMedia, $coverImageUrl = null)
    {
        // $videoMedia is already a resolved public URL (passed from sharePin via Media::url())
        $videoUrl = $this->resolveMediaUrl((string)$videoMedia);

        if (!$videoUrl) {
            return $this->errorResponse("No media provided for video pin.", "media");
        }

        // Download to a local temp file for the S3 multipart upload
        $localPath  = null;
        $tmpCreated = false;
        // Use curl stream-download to avoid loading large videos into memory
        $ext = strtolower(pathinfo(parse_url($videoUrl, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'mp4';
        $localPath = $this->downloadToTemp($videoUrl, $ext);
        if (!$localPath) {
            return $this->errorResponse("Could not download video file from: $videoUrl", "media");
        }
        $tmpCreated = true;

        // ── Step 1: Register media upload ────────────────────────────────────
        // NOTE: sendRequest() sends JSON. Pinterest /media returns upload_parameters
        // as an object with x-amz-* keys that S3 requires as form fields.
        // The file must NOT be deleted before we upload it — keep $localPath alive.
        $registerEndpoint = $this->baseApiUrl . "/media";
        $registerResponse = $this->sendRequest("POST", $registerEndpoint, [
            "media_type" => "video",
        ], $accessToken);

        if (!isset($registerResponse["media_id"])) {
            if ($tmpCreated) @unlink($localPath);
            $msg = $registerResponse["message"] ?? json_encode($registerResponse);
            return $this->errorResponse("Pinterest register failed: $msg", "media");
        }

        $mediaId      = $registerResponse["media_id"];
        $uploadUrl    = $registerResponse["upload_url"] ?? null;
        $uploadParams = $registerResponse["upload_parameters"] ?? [];

        if (!$uploadUrl) {
            if ($tmpCreated) @unlink($localPath);
            return $this->errorResponse("Pinterest did not return an upload URL.", "media");
        }

        // ── Step 2: Upload file to S3 ─────────────────────────────────────────
        // S3 pre-signed POST requires ALL upload_parameters fields to come BEFORE
        // the file field, in exact order returned by Pinterest. The Content-Type
        // field inside upload_parameters must be used — do NOT let Guzzle set its
        // own Content-Type header (it will conflict with the policy signature).
        //
        // BUG FIXED: Previously the file was deleted (tmpCreated unlink) before
        // this step, and the field order was wrong (file before params).

        $multipart = [];
        foreach ($uploadParams as $key => $value) {
            $multipart[] = ['name' => $key, 'contents' => (string)$value];
        }
        // File field MUST be last
        $multipart[] = [
            'name'     => 'file',
            'contents' => fopen($localPath, 'r'),
            'filename' => 'video.mp4',
        ];

        try {
            $guzzle = new Client(['verify' => false]);
            $s3Resp = $guzzle->request("POST", $uploadUrl, [
                'multipart' => $multipart,
                // Do NOT pass 'headers' => ['Content-Type' => ...] here —
                // Guzzle sets multipart/form-data automatically with correct boundary.
            ]);
            // S3 returns 204 on success; anything else is a failure
            $s3Status = $s3Resp->getStatusCode();
            if ($s3Status !== 204 && $s3Status !== 200 && $s3Status !== 201) {
                if ($tmpCreated) @unlink($localPath);
                return $this->errorResponse("S3 upload returned unexpected status: $s3Status", "media");
            }
        } catch (RequestException $e) {
            if ($tmpCreated) @unlink($localPath);
            $errBody = $e->hasResponse() ? (string)$e->getResponse()->getBody() : $e->getMessage();
            return $this->errorResponse("Video upload to S3 failed: $errBody", "media");
        }

        if ($tmpCreated) @unlink($localPath);

        // ── Step 3: Poll for processing ───────────────────────────────────────
        // Pinterest processes the video asynchronously after S3 upload.
        // Poll GET /v5/media/{media_id} until status = "succeeded".
        $pollEndpoint = $this->baseApiUrl . "/media/" . rawurlencode($mediaId);
        $maxAttempts  = 15; // 15 x 5s = 75s max
        $ready        = false;
        for ($i = 0; $i < $maxAttempts; $i++) {
            if ($i > 0) sleep(5);
            $statusResp = $this->sendRequest("GET", $pollEndpoint, [], $accessToken);
            $status     = $statusResp["status"] ?? null;
            if ($status === "succeeded") { $ready = true; break; }
            if ($status === "failed")    { return $this->errorResponse("Pinterest video processing failed.", "media"); }
        }
        if (!$ready) {
            return $this->errorResponse("Pinterest video took too long to process.", "media");
        }

        // ── Step 4: Create pin ────────────────────────────────────────────────
        // media_id must be passed as a string.
        // Pinterest REQUIRES cover_image_url or cover_image_key_frame_time for video pins.
        // If no custom thumbnail provided, we default to key_frame_time=0 (first frame).
        $pinParams = [
            "board_id"    => $boardId,
            "description" => $description,
            "alt_text"    => $description,
            "media_source" => [
                "source_type" => "video_id",
                "media_id"    => (string)$mediaId,
            ],
        ];

        // cover_image_url / cover_image_key_frame_time go INSIDE media_source
        if (!empty($coverImageUrl) && filter_var($coverImageUrl, FILTER_VALIDATE_URL)) {
            $pinParams["media_source"]["cover_image_url"] = $coverImageUrl;
        } else {
            $pinParams["media_source"]["cover_image_key_frame_time"] = 0;
        }

        if ($title !== "")                                           $pinParams["title"] = $title;
        if ($link !== "" && filter_var($link, FILTER_VALIDATE_URL)) $pinParams["link"]  = $link;

        \Log::error('Pinterest createPin payload', ['params' => json_encode($pinParams)]);

        return $this->sendRequest("POST", $this->baseApiUrl . "/pins", $pinParams, $accessToken);
    }

    /**
     * Sends an HTTP request using Guzzle.
     *
     * @param string      $method      HTTP method (GET, POST, etc.).
     * @param string      $endpoint    Fully qualified endpoint URL.
     * @param array       $params      Request parameters.
     * @param string|null $accessToken (Optional) Override token.
     * @return array
     */
    protected function sendRequest($method = "POST", $endpoint, array $params, string $accessToken = null)
    {
        try {
            if (!empty($accessToken) || !empty($this->accessToken)) {
                $token = !empty($accessToken) ? $accessToken : $this->accessToken;
                $headers = [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer $token"
                ];
                $options = [
                    'json' => $params,
                    'headers' => $headers
                ];
            } else {
                $token_string = base64_encode($this->app_id . ':' . $this->app_secret);
                $headers = [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => "Basic $token_string"
                ];
                $options = [
                    'form_params' => $params,
                    'headers' => $headers
                ];
            }

            $response = $this->client->request($method, $endpoint, $options);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody(), true);
            }
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Returns a standardized error response.
     *
     * @param string $message The error message.
     * @param string $type    The post type.
     * @return array Error response.
     */
    protected function errorResponse($message, $type)
    {
        return [
            "status"  => "error",
            "message" => $message,
            "type"    => $type,
        ];
    }
    // ── Media URL helpers (same logic as Post facades) ─────────────────────
    protected function resolveMediaUrl(string $media): string
    {
        $media = trim($media);
        if ($media === '') return '';
        if (filter_var($media, FILTER_VALIDATE_URL)) return $media;
        return \Media::url($media);
    }
    protected function mediaIsImage(string $media): bool
    {
        $p = parse_url($media, PHP_URL_PATH) ?: $media;
        return (bool) preg_match('/\.(jpe?g|png|gif|webp|bmp|svg|heic|heif)$/i', $p);
    }
    protected function mediaIsVideo(string $media): bool
    {
        $p = parse_url($media, PHP_URL_PATH) ?: $media;
        return (bool) preg_match('/\.(mp4|mov|avi|mkv|webm|flv|wmv|m4v|3gp|ogv)$/i', $p);
    }
    /**
     * Stream-download a URL to temp file via curl (memory-safe for large videos).
     */
    protected function downloadToTemp(string $url, string $ext = 'mp4'): ?string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'pin_dl_') . ".$ext";
        $fh  = fopen($tmp, 'wb');
        if (!$fh) return null;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fh,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 600,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); fclose($fh);
        if ($code === 200 && file_exists($tmp) && filesize($tmp) > 0) return $tmp;
        @unlink($tmp); return null;
    }


}