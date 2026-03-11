<?php
namespace Modules\AppChannelXProfiles\Classes;

class XApi
{
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $access_token;
    private $params;

    // ─── Constructor ─────────────────────────────────────────────────────────

    public function __construct($client_id = null, $client_secret = null, $redirect_uri = null)
    {
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri  = $redirect_uri;
        $this->params = [
            'response_type' => 'code',
            'client_id'     => $client_id,
            'redirect_uri'  => $this->redirect_uri,
            // media.write scope is REQUIRED for media uploads on v2
            'scope'         => 'tweet.read tweet.write users.read offline.access media.write',
        ];
    }

    // ─── Auth ─────────────────────────────────────────────────────────────────

    public function loginUrl()
    {
        return 'https://x.com/i/oauth2/authorize?' . http_build_query($this->params);
    }

    public function getAccessToken($code)
    {
        if (!$code) {
            return ['status' => '0', 'message' => __('Please enter X code')];
        }
        $params = [
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->redirect_uri,
        ];
        $result = json_decode($this->rawPost(
            'https://api.twitter.com/2/oauth2/token',
            http_build_query($params),
            ['Content-Type: application/x-www-form-urlencoded']
        ));
        if (isset($result->access_token)) {
            $this->access_token = $result->access_token;
            return $result->access_token;
        }
        return ['status' => '0', 'message' => $result->error_description ?? __('Unknown error')];
    }

    public function refreshToken($refresh_token)
    {
        if (!$refresh_token) {
            return ['status' => '0', 'message' => __('Please provide a valid refresh token')];
        }
        $params    = [
            'refresh_token' => $refresh_token,
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->client_id,
        ];
        $basicAuth = base64_encode("{$this->client_id}:{$this->client_secret}");
        $result    = json_decode($this->rawPost(
            'https://api.twitter.com/2/oauth2/token',
            http_build_query($params),
            [
                "Authorization: Basic {$basicAuth}",
                'Content-Type: application/x-www-form-urlencoded',
            ]
        ));
        if (isset($result->access_token)) {
            $this->access_token = $result->access_token;
            return $result; // full object so caller can persist the new token
        }
        return ['status' => '0', 'message' => $result->error_description ?? __('Unknown error')];
    }

    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    // ─── User info ────────────────────────────────────────────────────────────

    public function getUserInfo()
    {
        return json_decode($this->rawGet(
            'https://api.twitter.com/2/users/me?user.fields=id,name,username,profile_image_url',
            ["Authorization: Bearer {$this->access_token}"]
        ));
    }

    // ─── Post tweet ───────────────────────────────────────────────────────────

    public function postTweet($tweet_text, $media_ids = [])
    {
        $data = ['text' => $tweet_text];
        if (!empty($media_ids)) {
            // X requires media_ids as an array of strings
            $data['media'] = ['media_ids' => array_values(array_map('strval', $media_ids))];
        }
        $raw = $this->rawPost(
            'https://api.twitter.com/2/tweets',
            json_encode($data),
            [
                "Authorization: Bearer {$this->access_token}",
                'Content-Type: application/json',
            ]
        );
        return json_decode($raw);
    }

    // ─── Media upload (public entry point) ───────────────────────────────────

    /**
     * Uploads a media file to X API.
     *
     * Accepts either a local file path OR a public URL.
     * When given a URL, attempts to resolve it to a local storage path first
     * (avoiding HTTP overhead). Falls back to curl download if needed.
     *
     * Video upload uses dedicated chunked endpoints:
     *   INIT    : POST /2/media/upload/initialize  (JSON)
     *   APPEND  : POST /2/media/upload/{id}/append (multipart)
     *   FINALIZE: POST /2/media/upload/{id}/finalize
     *   STATUS  : GET  /2/media/upload/{id}
     * Image upload uses: POST /2/media/upload (multipart, single call)
     *
     * @param  string $media  Local path or public URL.
     * @return string|false   media_id string on success, false on any failure.
     */
    public function uploadMedia($media)
    {
        $tmpfile   = null;
        $file_path = null;

        if (filter_var($media, FILTER_VALIDATE_URL)) {
            // ── Try to resolve URL → local storage path first ──────────────
            $local = $this->urlToLocalPath($media);
            if ($local && file_exists($local) && filesize($local) > 0) {
                $file_path = $local;
                $ext       = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            } else {
                // ── Fall back: download via curl ───────────────────────────
                $ext     = strtolower(pathinfo(parse_url($media, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'mp4';
                $tmpfile = tempnam(sys_get_temp_dir(), 'xmedia_') . '.' . $ext;
                if (!$this->downloadFile($media, $tmpfile)) {
                    @unlink($tmpfile);
                    return false;
                }
                $file_path = $tmpfile;
            }
        } else {
            // ── Already a local path ───────────────────────────────────────
            if (!file_exists($media)) return false;
            $file_path = realpath($media);
            $ext       = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        }

        if (!$file_path || !file_exists($file_path) || filesize($file_path) === 0) {
            \Log::error('X uploadMedia: file missing or empty', [
                'input'     => $media,
                'file_path' => $file_path,
                'exists'    => $file_path ? file_exists($file_path) : false,
                'size'      => ($file_path && file_exists($file_path)) ? filesize($file_path) : 0,
            ]);
            if ($tmpfile) @unlink($tmpfile);
            return false;
        }

        \Log::error('X uploadMedia: resolved file', [
            'input'     => $media,
            'file_path' => $file_path,
            'size'      => filesize($file_path),
            'ext'       => $ext,
            'is_tmp'    => !is_null($tmpfile),
        ]);

        $image_exts = ['jpg', 'jpeg', 'png', 'webp', 'bmp'];
        $video_exts = ['mp4', 'mov', 'avi', 'wmv', 'webm', 'mkv', 'm4v'];
        $gif_exts   = ['gif'];

        if (in_array($ext, $image_exts)) {
            $result = $this->uploadImage($file_path);
        } elseif (in_array($ext, $video_exts) || in_array($ext, $gif_exts)) {
            $result = $this->uploadVideo($file_path, $ext);
        } else {
            \Log::error('X uploadMedia: unsupported extension', ['ext' => $ext]);
            if ($tmpfile) @unlink($tmpfile);
            return false;
        }

        if ($tmpfile) @unlink($tmpfile);
        return $result;
    }

    // ─── Private: resolve URL → local path ───────────────────────────────────

    /**
     * Converts a public storage URL to a local absolute path.
     *
     * Handles URLs like:
     *   https://example.com/storage/app/public/files/video.mp4
     *   https://example.com/storage/files/video.mp4
     *
     * Returns null if we can't map it.
     */
    private function urlToLocalPath($url)
    {
        $path = parse_url($url, PHP_URL_PATH); // e.g. /storage/app/public/files/video.mp4

        // Strip common public storage URL prefixes
        $prefixes = [
            '/storage/app/public/',
            '/storage/',
        ];

        foreach ($prefixes as $prefix) {
            if (strpos($path, $prefix) === 0) {
                $relative = substr($path, strlen($prefix)); // e.g. files/video.mp4
                // Laravel storage_path('app/public/...')
                $candidates = [
                    storage_path('app/public/' . $relative),
                    storage_path('app/' . $relative),
                    public_path('storage/' . $relative),
                    base_path('storage/app/public/' . $relative),
                ];
                foreach ($candidates as $candidate) {
                    if (file_exists($candidate)) return $candidate;
                }
            }
        }
        return null;
    }

    // ─── Private: curl-based file download ───────────────────────────────────

    /**
     * Downloads a URL to a local file using curl.
     * More reliable than file_get_contents for large files.
     */
    private function downloadFile($url, $dest)
    {
        $fp   = fopen($dest, 'wb');
        if (!$fp) return false;

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_FILE           => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 300,  // 5 min timeout for large videos
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_USERAGENT      => 'Mozilla/5.0',
        ]);
        $ok = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        fclose($fp);

        return $ok && $httpCode === 200 && file_exists($dest) && filesize($dest) > 0;
    }

    // ─── Private: image upload (simple, no chunking) ─────────────────────────

    private function uploadImage($file_path)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.x.com/2/media/upload',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_SAFE_UPLOAD    => true,
            CURLOPT_POSTFIELDS     => [
                'media'          => new \CURLFile($file_path),
                'media_category' => 'tweet_image',
            ],
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$this->access_token}"],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $raw = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($raw);
        // Success response: { "data": { "id": "...", "media_key": "..." } }
        return $result->data->id ?? false;
    }

    // ─── Private: chunked video/GIF upload ───────────────────────────────────

    /**
     * INIT → APPEND (1 MB chunks) → FINALIZE → STATUS poll
     * All steps POST multipart/form-data to https://api.x.com/2/media/upload
     * with 'command' as a form field.
     */
    /**
     * Uses ffprobe to count audio streams in a video file.
     * X API rejects videos with no audio track.
     */
    private function videoHasAudio($file_path)
    {
        $cmd    = 'ffprobe -v error -select_streams a -show_entries stream=codec_type -of csv=p=0 ' . escapeshellarg($file_path) . ' 2>/dev/null';
        $output = trim(shell_exec($cmd) ?? '');
        return $output !== '';
    }

    /**
     * Transcodes any video to a proper H.264/AAC mp4 container.
     * X rejects .mov and other containers even if the codec is H.264.
     * Returns path to transcoded temp file, or null on failure.
     */
    private function transcodeToMp4($file_path)
    {
        $out = tempnam(sys_get_temp_dir(), 'xmp4_') . '.mp4';
        // -c:v libx264  → re-encode video to H.264
        // -c:a aac      → re-encode/add audio to AAC
        // -movflags +faststart → optimize for streaming
        $cmd = 'ffmpeg -y -i ' . escapeshellarg($file_path)
             . ' -c:v libx264 -preset fast -crf 23'
             . ' -c:a aac -b:a 128k'
             . ' -movflags +faststart'
             . ' ' . escapeshellarg($out)
             . ' 2>/dev/null';
        shell_exec($cmd);
        if (file_exists($out) && filesize($out) > 0) {
            return $out;
        }
        @unlink($out);
        return null;
    }

    /**
     * Adds a silent stereo AAC audio track to a video using FFmpeg.
     * Returns path to the fixed temp file, or null on failure.
     */
    private function addSilentAudio($file_path)
    {
        $out = tempnam(sys_get_temp_dir(), 'xvideo_') . '.mp4';
        // -c:v copy  → keep original video stream untouched
        // anullsrc   → generate silent audio
        // -c:a aac   → encode as AAC (required by X)
        // -shortest  → end when video ends
        $cmd = 'ffmpeg -y -i ' . escapeshellarg($file_path)
             . ' -f lavfi -i anullsrc=r=44100:cl=stereo'
             . ' -c:v copy -c:a aac -b:a 128k -shortest'
             . ' ' . escapeshellarg($out)
             . ' 2>/dev/null';
        shell_exec($cmd);
        if (file_exists($out) && filesize($out) > 0) {
            return $out;
        }
        @unlink($out);
        return null;
    }

    private function uploadVideo($file_path, $ext)
    {
        // ── Correct X API v2 chunked video upload endpoints ───────────────────
        // INIT    : POST /2/media/upload/initialize        (JSON body)
        // APPEND  : POST /2/media/upload/{id}/append       (multipart/form-data)
        // FINALIZE: POST /2/media/upload/{id}/finalize     (empty POST)
        // STATUS  : GET  /2/media/upload/{id}              (no body)
        // Source  : https://docs.x.com/x-api/media/initialize-media-upload

        $media_type     = ($ext === 'gif') ? 'image/gif' : 'video/mp4';
        $media_category = ($ext === 'gif') ? 'tweet_gif' : 'tweet_video';

        // ── Auto-fix 1: convert .mov to .mp4 (X only accepts mp4 container) ──
        $fixedTmp = null;
        if ($ext === 'mov') {
            $mp4 = $this->transcodeToMp4($file_path);
            if ($mp4) {
                $fixedTmp  = $mp4;
                $file_path = $mp4;
                $ext       = 'mp4';
                $media_type = 'video/mp4';
            }
        }

        // ── Auto-fix 2: add silent audio if video has none ───────────────────
        if ($ext !== 'gif' && !$this->videoHasAudio($file_path)) {
            $fixed = $this->addSilentAudio($file_path);
            if ($fixed) {
                // Replace fixedTmp with the audio-fixed version
                if ($fixedTmp) @unlink($fixedTmp);
                $fixedTmp  = $fixed;
                $file_path = $fixed;
            }
        }

        $size = filesize($file_path);

        // ── INIT: POST /2/media/upload/initialize  (JSON body) ───────────────
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.x.com/2/media/upload/initialize',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'media_type'     => $media_type,
                'total_bytes'    => $size,
                'media_category' => $media_category,
            ]),
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->access_token}",
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $raw      = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($curl);
        curl_close($curl);

        \Log::error('X INIT', [
            'http_code'  => $httpCode,
            'curl_error' => $curlErr,
            'response'   => $raw,
        ]);

        $result   = json_decode($raw);
        $media_id = $result->data->id ?? null;
        if (!$media_id) {
            if ($fixedTmp) @unlink($fixedTmp);
            return false;
        }

        // ── APPEND: POST /2/media/upload/{id}/append  (multipart/form-data) ──
        $fp            = fopen($file_path, 'rb');
        $segment_index = 0;
        $chunk_size    = 1 * 1024 * 1024; // 1 MB

        while (!feof($fp)) {
            $chunk = fread($fp, $chunk_size);
            if ($chunk === false || strlen($chunk) === 0) break;

            $chunk_tmp = tempnam(sys_get_temp_dir(), 'xchunk_');
            file_put_contents($chunk_tmp, $chunk);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://api.x.com/2/media/upload/{$media_id}/append",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_SAFE_UPLOAD    => true,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_POSTFIELDS     => [
                    'media'         => new \CURLFile($chunk_tmp, 'application/octet-stream', 'chunk'),
                    'segment_index' => (string) $segment_index,
                ],
                CURLOPT_HTTPHEADER     => [
                    "Authorization: Bearer {$this->access_token}",
                ],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);
            $appendRaw = curl_exec($curl);
            $httpCode  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlErr   = curl_error($curl);
            curl_close($curl);
            @unlink($chunk_tmp);

            if ($segment_index === 0) {
                \Log::error('X APPEND seg 0', [
                    'http_code'  => $httpCode,
                    'curl_error' => $curlErr,
                    'response'   => $appendRaw,
                ]);
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                \Log::error('X APPEND failed', [
                    'segment'   => $segment_index,
                    'http_code' => $httpCode,
                    'response'  => $appendRaw,
                ]);
                fclose($fp);
                if ($fixedTmp) @unlink($fixedTmp);
                return false;
            }

            $segment_index++;
        }
        fclose($fp);

        // ── FINALIZE: POST /2/media/upload/{id}/finalize ─────────────────────
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => "https://api.x.com/2/media/upload/{$media_id}/finalize",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => '',
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$this->access_token}",
                'Content-Length: 0',
            ],
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $raw    = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($raw);

        \Log::error('X FINALIZE', ['response' => $raw]);

        if (!isset($result->data->processing_info)) {
            if ($fixedTmp) @unlink($fixedTmp);
            return $media_id;
        }

        $state = $result->data->processing_info->state ?? 'succeeded';

        // ── STATUS: GET /2/media/upload/{id} ─────────────────────────────────
        $tries    = 0;
        $maxTries = 30;
        while (in_array($state, ['pending', 'in_progress']) && $tries < $maxTries) {
            $wait = (int) ($result->data->processing_info->check_after_secs ?? 3);
            sleep($wait);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://api.x.com/2/media/upload?command=STATUS&media_id={$media_id}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTPHEADER     => [
                    "Authorization: Bearer {$this->access_token}",
                ],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $raw    = curl_exec($curl);
            curl_close($curl);
            \Log::error('X STATUS poll', ['http_code' => $http_code, 'response' => $raw]);
            $result = json_decode($raw);
            $state  = $result->data->processing_info->state ?? 'succeeded';
            $tries++;
        }

        \Log::error('X STATUS final', ['state' => $state, 'response' => $raw]);

        if ($fixedTmp) @unlink($fixedTmp);
        return ($state === 'succeeded') ? $media_id : false;
    }

            // ─── Raw HTTP helpers ─────────────────────────────────────────────────────

    private function rawPost($url, $body, array $headers)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function rawGet($url, array $headers)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    // Kept for backward compatibility
    public function curlGet($url)
    {
        return json_decode($this->rawGet($url, ["Authorization: Bearer {$this->access_token}"]));
    }

    public function curlPost($url, $postFields)
    {
        return json_decode($this->rawPost($url, $postFields, [
            "Authorization: Bearer {$this->access_token}",
            'Content-Type: application/json',
        ]));
    }
}