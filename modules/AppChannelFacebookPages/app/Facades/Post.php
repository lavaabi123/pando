<?php

namespace Modules\AppChannelFacebookPages\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use JanuSoftware\Facebook\Facebook;
use Media;

class Post extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    public static function validator($post)
    {
        $errors = [];
        $data   = json_decode($post->data, false);
        $medias  = $data->medias ?? [];
        $options = $data->options ?? null;

        if ($options && ($options->fb_type ?? null) === 'reels') {
            if (empty($medias) || !self::mediaIsVideo(self::resolveMediaUrl((string)($medias[0] ?? '')))) {
                $errors[] = __("Facebook Reels only supports posting videos (3–90 seconds).");
            }
        }

        return $errors;
    }

    public static function post($post)
    {
        $FB = new Facebook([
            'app_id'                => get_option("facebook_app_id", ""),
            'app_secret'            => get_option("facebook_app_secret", ""),
            'default_graph_version' => get_option("facebook_graph_version", "v21.0"),
        ]);

        $data     = json_decode($post->data);
        $medias   = $data->medias ?? [];
        $endpoint = "/" . $post->account->pid . "/";
        $caption  = spintax($data->caption ?? '');
        $postType = $data->options->fb_type ?? "default";

        $customThumb = $data->custom_thumbnail ?? null;

        try {
            if ($post->account->login_type != 1) {
                return [
                    "status"  => 0,
                    "message" => __("Unsupported account login type"),
                    "type"    => $post->type,
                ];
            }

            return match ($postType) {
                'reels' => self::handleReels($FB, $post, $data, $medias, $endpoint, $caption, $customThumb),
                default => self::handleDefault($FB, $post, $data, $medias, $endpoint, $caption, $customThumb),
            };
        } catch (\Exception $e) {
            if ($e->getCode() == 190) {
                DB::table("accounts")
                    ->where("id", $post->account->id)
                    ->update(["status" => 0]);
            }

            $response = method_exists($e, 'getResponseData') ? $e->getResponseData() : null;
            if (is_array($response['error'] ?? null)) {
                $fbErr = $response['error'];
                $msg   = $fbErr['error_user_msg'] ?? $fbErr['message'] ?? $e->getMessage();
                $title = $fbErr['error_user_title'] ?? null;
                return [
                    "status"  => 0,
                    "message" => $title ? "$title: $msg" : $msg,
                    "type"    => $post->type,
                ];
            }

            return [
                "status"  => 0,
                "message" => $e->getMessage(),
                "type"    => $post->type,
            ];
        }
    }

    // ── REELS ─────────────────────────────────────────────────────────────────

    protected static function handleReels($FB, $post, $data, $medias, $endpoint, $caption, $customThumb = null)
    {
        switch ($post->type) {
            case 'media':
                if (empty($medias) || !self::mediaIsVideo(self::resolveMediaUrl((string)($medias[0] ?? '')))) {
                    return [
                        "status"  => 0,
                        "message" => __("Facebook Reels only support video posts."),
                        "type"    => $post->type,
                    ];
                }

                $uploadParams  = [
                    "upload_phase" => "start",
                    "access_token" => $post->account->token,
                ];
                $uploadSession = $FB->post($endpoint . 'video_reels', $uploadParams, $post->account->token)
                    ->getDecodedBody();

                if (empty($uploadSession['video_id'])) {
                    return [
                        "status"  => 0,
                        "message" => __("Could not create upload session for Reels."),
                        "type"    => $post->type,
                    ];
                }

                return self::completeReelsUpload($FB, $post, $uploadSession, $caption, $medias[0], $endpoint, $customThumb);

            case 'link':
                return ["status" => 0, "message" => __("Facebook Reels do not support link posts."), "type" => $post->type];
            case 'text':
                return ["status" => 0, "message" => __("Facebook Reels do not support text-only posts."), "type" => $post->type];
            default:
                return ["status" => 0, "message" => __("Unknown Reels post type."), "type" => $post->type];
        }
    }

    protected static function completeReelsUpload($FB, $post, $uploadSession, $caption, $mediaIdentifier, $endpoint, $customThumb = null)
    {
        $videoId   = $uploadSession['video_id'];
        $localPath = self::resolveLocalPath($mediaIdentifier);

        if (!$localPath) {
            return [
                "status"  => 0,
                "message" => __("Could not read video file for upload."),
                "type"    => $post->type,
            ];
        }

        $mimeType  = mime_content_type($localPath) ?: 'video/mp4';
        $curlVideo = new \CURLFile($localPath, $mimeType, basename($localPath));

        $uploadResponse = $FB->post("/$videoId", [
            'source' => $curlVideo,
        ], $post->account->token)->getDecodedBody();

        if (empty($uploadResponse['success']) || $uploadResponse['success'] != 1) {
            return [
                "status"  => 0,
                "message" => __("File upload failed."),
                "type"    => $post->type,
            ];
        }

        $publishParams = [
            "upload_phase" => "finish",
            "video_id"     => $videoId,
            "description"  => $caption,
        ];
        $FB->post($endpoint . 'video_reels', $publishParams, $post->account->token);

        // Upload thumbnail via /{video_id}/thumbnails AFTER publish
        if ($customThumb) {
            self::uploadThumbnail($FB, $videoId, $customThumb, $post->account->token);
        }

        return [
            "status"  => 1,
            "message" => __("Success"),
            "id"      => $videoId,
            "url"     => "https://www.facebook.com/reel/",
            "type"    => "reels",
        ];
    }

    // ── DEFAULT (feed / videos / photos) ─────────────────────────────────────

    protected static function handleDefault($FB, $post, $data, $medias, $endpoint, $caption, $customThumb = null)
    {
        // Single video: handle separately so we can attach thumbnail after posting
        if ($post->type === 'media' && count($medias) === 1 && self::mediaIsVideo(self::resolveMediaUrl((string)($medias[0] ?? '')))) {
            return self::handleVideoPost($FB, $post, $medias[0], $caption, $endpoint, $customThumb);
        }

        [$ep, $params] = self::handleDefaultPost($FB, $post, $data, $medias, $caption, $endpoint);

        if (empty($ep) || !is_string($ep)) {
            return [
                "status"  => 0,
                "message" => __("Media not found or unsupported media type."),
                "type"    => $post->type,
            ];
        }

        $response = $FB->post($ep, $params, $post->account->token)->getDecodedBody();
        $postId   = $response['id'] ?? null;

        return [
            "status"  => 1,
            "message" => __("Success"),
            "id"      => $postId,
            "url"     => $postId ? "https://fb.com/$postId" : null,
            "type"    => $post->type,
        ];
    }

    /**
     * Upload a video using Facebook's Resumable Upload API (chunked) via raw cURL.
     *
     * WHY raw cURL: The FB PHP SDK routes all requests to graph.facebook.com.
     * Resumable video uploads MUST go to graph-video.facebook.com — using the
     * wrong host returns "An unknown error has occurred." We bypass the SDK for
     * the three video-upload phases and use the SDK only for the thumbnail call.
     *
     * Flow:
     *   1. POST graph-video.facebook.com/{page_id}/videos  upload_phase=start
     *   2. POST graph-video.facebook.com/{page_id}/videos  upload_phase=transfer  (loop)
     *   3. POST graph-video.facebook.com/{page_id}/videos  upload_phase=finish
     *   4. Poll graph.facebook.com/{video_id}?fields=status  until ready
     *   5. POST graph.facebook.com/{video_id}/thumbnails    source=binary
     */
    protected static function handleVideoPost($FB, $post, $mediaIdentifier, $caption, $endpoint, $customThumb = null)
    {
        $localPath = self::resolveLocalPath($mediaIdentifier);

        if (!$localPath || !file_exists($localPath)) {
            return [
                "status"  => 0,
                "message" => __("Could not read video file for upload."),
                "type"    => $post->type,
            ];
        }

        $fileSize  = filesize($localPath);
        $chunkSize = 10 * 1024 * 1024; // 10 MB per chunk
        $token     = $post->account->token;
        $pageId    = $post->account->pid;

        // Graph API version — read from FB instance or fall back to v19.0
        $graphVersion = method_exists($FB, 'getDefaultGraphVersion')
            ? $FB->getDefaultGraphVersion()
            : 'v19.0';

        $videoBase = "https://graph-video.facebook.com/{$graphVersion}/{$pageId}/videos";

        // ── Phase 1: Start ────────────────────────────────────────────────────
        $startBody = self::curlPost($videoBase, [
            'upload_phase' => 'start',
            'file_size'    => $fileSize,
            'access_token' => $token,
        ]);

        if (isset($startBody['error'])) {
            $msg = $startBody['error']['message'] ?? 'Could not start video upload session.';
            \Log::error("[FB Video] Phase 1 (start) error: " . json_encode($startBody['error']));
            return ["status" => 0, "message" => $msg, "type" => $post->type];
        }

        $uploadSessionId = $startBody['upload_session_id'] ?? null;
        $videoId         = $startBody['video_id'] ?? null;
        $startOffset     = (int) ($startBody['start_offset'] ?? 0);

        if (!$uploadSessionId || !$videoId) {
            \Log::error("[FB Video] Phase 1 missing session/video ID: " . json_encode($startBody));
            return ["status" => 0, "message" => __("Could not start video upload session."), "type" => $post->type];
        }

        // ── Phase 2: Transfer chunks ──────────────────────────────────────────
        $handle = fopen($localPath, 'rb');
        if (!$handle) {
            return ["status" => 0, "message" => __("Could not open video file for reading."), "type" => $post->type];
        }

        fseek($handle, $startOffset);
        $chunkIndex = 0;

        while (!feof($handle)) {
            $chunkData = fread($handle, $chunkSize);
            if ($chunkData === false || strlen($chunkData) === 0) break;

            $chunkFile = tempnam(sys_get_temp_dir(), 'fb_chunk_');
            file_put_contents($chunkFile, $chunkData);

            $transferBody = self::curlPost($videoBase, [
                'upload_phase'      => 'transfer',
                'upload_session_id' => $uploadSessionId,
                'start_offset'      => $startOffset,
                'access_token'      => $token,
                'video_file_chunk'  => new \CURLFile($chunkFile, 'application/octet-stream', 'chunk'),
            ]);

            @unlink($chunkFile);

            if (isset($transferBody['error'])) {
                fclose($handle);
                $msg = $transferBody['error']['message'] ?? 'Chunk upload failed.';
                \Log::error("[FB Video] Phase 2 chunk {$chunkIndex} error: " . json_encode($transferBody['error']));
                return ["status" => 0, "message" => $msg, "type" => $post->type];
            }

            $startOffset = (int) ($transferBody['start_offset'] ?? ($startOffset + strlen($chunkData)));
            $chunkIndex++;
        }

        fclose($handle);

        // ── Phase 3: Finish (with thumb if available) ─────────────────────────
        // Pass the thumbnail as a binary 'thumb' CURLFile during the finish phase.
        // This is the most reliable method — Facebook processes the thumbnail
        // atomically with the video upload session.
        $finishFields = [
            'upload_phase'      => 'finish',
            'upload_session_id' => $uploadSessionId,
            'description'       => $caption,
            'access_token'      => $token,
        ];

        $thumbTmpFile = null;
        if ($customThumb) {
            $thumbUrl = preg_match('#^https?://#', $customThumb)
                ? $customThumb
                : rtrim(config('app.url'), '/') . '/' . ltrim($customThumb, '/');

            $thumbBytes = @file_get_contents($thumbUrl);

            if ($thumbBytes && strlen($thumbBytes) > 100 && substr($thumbBytes, 0, 3) === "\xFF\xD8\xFF") {
                $thumbTmpFile = tempnam(sys_get_temp_dir(), 'fb_thumb_') . '.jpg';
                file_put_contents($thumbTmpFile, $thumbBytes);
                $finishFields['thumb'] = new \CURLFile($thumbTmpFile, 'image/jpeg', 'thumbnail.jpg');
            } else {
            }
        }

        $finishBody = self::curlPost($videoBase, $finishFields);

        if ($thumbTmpFile) @unlink($thumbTmpFile);

        if (isset($finishBody['error'])) {
            $msg = $finishBody['error']['message'] ?? 'Video finish phase failed.';
            \Log::error("[FB Video] Phase 3 (finish) error: " . json_encode($finishBody['error']));
            return ["status" => 0, "message" => $msg, "type" => $post->type];
        }

        // ── Phase 4+5: /thumbnails endpoint as additional fallback ─────────────
        if ($customThumb) {
            self::uploadThumbnail($FB, $videoId, $customThumb, $token);
        }

        return [
            "status"  => 1,
            "message" => __("Success"),
            "id"      => $videoId,
            "url"     => "https://fb.com/{$videoId}",
            "type"    => $post->type,
        ];
    }

    /**
     * Minimal cURL POST helper — used for graph-video.facebook.com requests
     * which the FB SDK would incorrectly route to graph.facebook.com.
     */
    protected static function curlPost(string $url, array $fields): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $fields,   // array with CURLFile triggers multipart/form-data
            CURLOPT_SAFE_UPLOAD    => true,       // required for CURLFile in PHP 5.6+
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 300,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw    = curl_exec($ch);
        $err    = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            \Log::error("[FB cURL] error: {$err}");
            return ['error' => ['message' => "cURL error: {$err}"]];
        }

        return json_decode($raw, true) ?? ['error' => ['message' => "Invalid JSON (HTTP {$status}): " . substr($raw, 0, 200)]];
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    /**
     * Resolve a media identifier to a local filesystem path.
     * For remote storage (S3/Contabo): downloads to a temp file and returns that path.
     * For local storage: returns the real path directly (no copy needed).
     * Returns null on failure.
     */
    protected static function resolveLocalPath($mediaIdentifier): ?string
    {
        try {
            $storageType = get_option('file_storage_server', 'local');

            if ($storageType === 's3' || $storageType === 'contabo') {
                $remoteUrl = self::resolveMediaUrl($mediaIdentifier);
                $tmpFile   = tempnam(sys_get_temp_dir(), 'fb_vid_') . '.mp4';
                $bytes     = @file_get_contents($remoteUrl);
                if (!$bytes) return null;
                file_put_contents($tmpFile, $bytes);
                return $tmpFile;
            } else {
                $relativePath = self::toRelativePath($mediaIdentifier);
                $path         = Storage::disk('public')->path($relativePath);
                return file_exists($path) ? $path : null;
            }
        } catch (\Throwable $e) {
            \Log::warning('[FB Video] resolveLocalPath failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload a custom thumbnail via POST /{video_id}/thumbnails.
     * Docs: https://developers.facebook.com/docs/graph-api/reference/video-thumbnail/
     *
     * IMPORTANT: Facebook requires the video to be fully processed (video_status = "ready")
     * before a thumbnail can be set. We poll GET /{video_id}?fields=status up to 30 times
     * with 5s intervals (max 150s) before giving up.
     */
    protected static function uploadThumbnail(Facebook $FB, string $videoId, string $thumbIdentifier, string $token): void
    {
        try {
            // ── Wait for video to be ready ────────────────────────────────────
            // Poll with backoff: 2s, 3s, 3s, 5s, 5s, 5s... up to 15 attempts (~60s max)
            $pollIntervals = [2, 3, 3, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5];
            $maxAttempts   = count($pollIntervals) + 1;
            $ready         = false;

            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                if ($attempt > 0) sleep($pollIntervals[$attempt - 1] ?? 5);

                try {
                    $statusBody = $FB->get(
                        "/{$videoId}?fields=status",
                        $token
                    )->getDecodedBody();

                    $videoStatus = $statusBody['status']['video_status'] ?? null;

                    if ($videoStatus === 'ready') {
                        $ready = true;
                        break;
                    }

                    if ($videoStatus === 'error') {
                        \Log::warning("[FB Thumbnail] Video {$videoId} processing failed — skipping thumbnail.");
                        return;
                    }

                    // still "processing" — keep polling

                } catch (\Throwable $pollErr) {
                    \Log::warning("[FB Thumbnail] Status poll error: " . $pollErr->getMessage());
                }
            }

            if (!$ready) {
                \Log::warning("[FB Thumbnail] Video {$videoId} not ready after {$maxAttempts} attempts (~60s) — skipping thumbnail.");
                return;
            }

            // ── Resolve thumbnail to a public URL then download to temp file ──
            // Avoids all local path resolution issues — we always download the
            // bytes from the public URL regardless of storage driver.

            // Ensure we have a full URL
            if (!preg_match('#^https?://#', $thumbIdentifier)) {
                $thumbUrl = rtrim(config('app.url'), '/') . '/' . ltrim($thumbIdentifier, '/');
            } else {
                $thumbUrl = $thumbIdentifier;
            }

            $bytes = @file_get_contents($thumbUrl);

            if (!$bytes || strlen($bytes) < 100) {
                \Log::warning("[FB Thumbnail] Could not download thumbnail or file too small (size=" . strlen((string)$bytes) . "): {$thumbUrl}");
                return;
            }

            // Verify it's actually a JPEG (starts with FF D8 FF)
            $header = substr($bytes, 0, 3);
            if ($header !== "\xFF\xD8\xFF") {
                \Log::warning("[FB Thumbnail] Downloaded file is not a valid JPEG. First bytes: " . bin2hex($header));
                return;
            }

            $tmpFile = tempnam(sys_get_temp_dir(), 'fb_thumb_') . '.jpg';
            file_put_contents($tmpFile, $bytes);

            // ── POST /{video_id}/thumbnails via raw cURL ──────────────────────
            // The FB SDK mishandles CURLFile multipart for this endpoint.

            $graphVersion = method_exists($FB, 'getDefaultGraphVersion')
                ? $FB->getDefaultGraphVersion()
                : 'v19.0';

            $thumbApiUrl = "https://graph.facebook.com/{$graphVersion}/{$videoId}/thumbnails";

            $thumbResp = self::curlPost($thumbApiUrl, [
                'source'        => new \CURLFile($tmpFile, 'image/jpeg', 'thumbnail.jpg'),
                'is_preferred'  => 'true',
                'access_token'  => $token,
            ]);

            @unlink($tmpFile);

        } catch (\Throwable $e) {
            \Log::warning("[FB Thumbnail] " . $e->getMessage());
        }
    }

    /**
     * Strip any leading storage URL prefix and return a path relative to
     * Storage::disk('public') root (i.e. storage/app/public/).
     *
     * The public disk web URL is: https://domain.com/storage/app/public/{path}
     * OR (with storage:link symlink):  https://domain.com/storage/{path}
     *
     * Storage::disk('public')->path($relative) prepends storage/app/public/,
     * so $relative must NOT include app/public/.
     *
     * Examples:
     *   https://domain.com/storage/app/public/thumbnails/x.jpg  → thumbnails/x.jpg
     *   https://domain.com/storage/thumbnails/x.jpg             → thumbnails/x.jpg
     *   /storage/app/public/thumbnails/x.jpg                    → thumbnails/x.jpg
     *   /storage/thumbnails/x.jpg                               → thumbnails/x.jpg
     *   thumbnails/x.jpg                                        → thumbnails/x.jpg
     */
    protected static function toRelativePath(string $identifier): string
    {
        $appUrl = rtrim(config('app.url'), '/');

        // Full URL with /storage/app/public/ (symlinked public disk via app/public subfolder)
        $prefix1 = $appUrl . '/storage/app/public/';
        if (str_starts_with($identifier, $prefix1)) {
            return substr($identifier, strlen($prefix1));
        }

        // Full URL with /storage/ (symlinked public disk — storage link points to storage/app/public)
        $prefix2 = $appUrl . '/storage/';
        if (str_starts_with($identifier, $prefix2)) {
            return substr($identifier, strlen($prefix2));
        }

        // Absolute path with /storage/app/public/
        if (str_starts_with($identifier, '/storage/app/public/')) {
            return substr($identifier, strlen('/storage/app/public/'));
        }

        // Absolute path with /storage/
        if (str_starts_with($identifier, '/storage/')) {
            return substr($identifier, strlen('/storage/'));
        }

        return ltrim($identifier, '/');
    }

    protected static function handleDefaultPost($FB, $post, $data, $medias, $caption, $endpoint)
    {
        switch ($post->type) {
            case 'media':
                return self::handleMediaPost($FB, $post, $medias, $caption, $endpoint);
            case 'link':
                return [$endpoint . "feed", ['message' => $caption, 'link' => $data->link]];
            case 'text':
                return [$endpoint . "feed", ['message' => $caption]];
            default:
                return [null, []];
        }
    }

    protected static function handleMediaPost($FB, $post, $medias, $caption, $endpoint)
    {
        if (count($medias) === 1) {
            $media = $medias[0];

            $mediaUrl = self::resolveMediaUrl((string)$media);
            if (self::mediaIsImage($mediaUrl)) {
                return [$endpoint . "photos", [
                    'message' => $caption,
                    'url'     => $mediaUrl,
                ]];
            }

            if (self::mediaIsVideo($mediaUrl)) {
                // Single video goes through handleVideoPost — should not reach here
                return [$endpoint . "videos", [
                    'description' => $caption,
                    'file_url'    => $mediaUrl,
                ]];
            }

            return [null, []];
        }

        // Multiple images carousel
        $mediaIds = [];
        $count    = 0;
        foreach ($medias as $media) {
            $mediaUrl = self::resolveMediaUrl((string)$media);
            if (self::mediaIsImage($mediaUrl)) {
                $upload = $FB->post($endpoint . 'photos', [
                    'url'       => $mediaUrl,
                    'published' => false,
                ], $post->account->token)->getDecodedBody();

                if (!empty($upload['id'])) {
                    $mediaIds['attached_media[' . $count . ']'] = '{"media_fbid":"' . $upload['id'] . '"}';
                    $count++;
                }
            }
        }

        $params = ['message' => $caption] + $mediaIds;
        return [$endpoint . "feed", $params];
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