<?php
namespace Modules\AppChannelFacebookPages\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;
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
            if (empty($medias) || !Media::isVideo($medias[0])) {
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
                if (empty($medias) || !Media::isVideo($medias[0])) {
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
                return self::completeReelsUpload($FB, $post, $uploadSession, $caption, Media::url($medias[0]), $endpoint, $customThumb);

            case 'link':
                return ["status" => 0, "message" => __("Facebook Reels do not support link posts."), "type" => $post->type];
            case 'text':
                return ["status" => 0, "message" => __("Facebook Reels do not support text-only posts."), "type" => $post->type];
            default:
                return ["status" => 0, "message" => __("Unknown Reels post type."), "type" => $post->type];
        }
    }

    protected static function completeReelsUpload($FB, $post, $uploadSession, $caption, $mediaUrl, $endpoint, $customThumb = null)
    {
        $videoId = $uploadSession['video_id'];

        $uploadResponse = $FB->post("/$videoId", [
            'file_url' => $mediaUrl,
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

        // Upload thumbnail via correct endpoint AFTER video is published
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
        // For single video posts we handle thumbnail separately after posting
        if ($post->type === 'media' && count($medias) === 1 && Media::isVideo($medias[0])) {
            return self::handleVideoPost($FB, $post, $medias[0], $caption, $endpoint, $customThumb);
        }

        [$endpoint, $params] = self::handleDefaultPost($FB, $post, $data, $medias, $caption, $endpoint);

        if (empty($endpoint) || !is_string($endpoint)) {
            return [
                "status"  => 0,
                "message" => __("Media not found or unsupported media type."),
                "type"    => $post->type,
            ];
        }

        $response = $FB->post($endpoint, $params, $post->account->token)->getDecodedBody();
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
     * Post a single video, then upload the custom thumbnail via
     * POST /{video_id}/thumbnails (the correct Facebook API).
     */
    protected static function handleVideoPost($FB, $post, $media, $caption, $endpoint, $customThumb = null)
    {
        $params = [
            'description' => $caption,
            'file_url'    => Media::url($media),
        ];

        $response = $FB->post($endpoint . 'videos', $params, $post->account->token)->getDecodedBody();
        $videoId  = $response['id'] ?? null;

        if (!$videoId) {
            return [
                "status"  => 0,
                "message" => __("Video post failed — no video ID returned."),
                "type"    => $post->type,
            ];
        }

        // Now upload thumbnail via the official /{video_id}/thumbnails endpoint
        if ($customThumb) {
            self::uploadThumbnail($FB, $videoId, $customThumb, $post->account->token);
        }

        return [
            "status"  => 1,
            "message" => __("Success"),
            "id"      => $videoId,
            "url"     => "https://fb.com/$videoId",
            "type"    => $post->type,
        ];
    }

    /**
     * Upload a custom thumbnail to Facebook using the correct API:
     * POST /{video_id}/thumbnails
     *   source      = binary image file (multipart)
     *   is_preferred = true  → sets it as the displayed thumbnail
     *
     * Docs: https://developers.facebook.com/docs/graph-api/reference/video-thumbnail/
     */
    protected static function uploadThumbnail(Facebook $FB, string $videoId, string $thumbUrl, string $token): void
    {
        try {
            // Build absolute URL if stored as a relative path
            if (!preg_match('#^https?://#', $thumbUrl)) {
                $thumbUrl = rtrim(config('app.url'), '/') . '/' . ltrim($thumbUrl, '/');
            }

            // Download thumbnail to a temp file
            $tmpFile = tempnam(sys_get_temp_dir(), 'fb_thumb_') . '.jpg';
            $bytes   = @file_get_contents($thumbUrl);
            if (!$bytes) {
                \Log::warning("[FB Thumbnail] Could not download: $thumbUrl");
                return;
            }
            file_put_contents($tmpFile, $bytes);

            // POST /{video_id}/thumbnails with source = CURLFile (binary)
            $FB->post("/$videoId/thumbnails", [
                'source'       => new \CURLFile($tmpFile, 'image/jpeg', 'thumbnail.jpg'),
                'is_preferred' => true,
            ], $token);

            @unlink($tmpFile);
        } catch (\Throwable $e) {
            // Thumbnail failure must not fail the whole post
            \Log::warning("[FB Thumbnail] Upload failed: " . $e->getMessage());
            @unlink($tmpFile ?? '');
        }
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

            if (Media::isImg($media)) {
                return [$endpoint . "photos", [
                    'message' => $caption,
                    'url'     => Media::url($media),
                ]];
            }

            // Single video without custom thumb (thumbnail handled in handleVideoPost)
            if (Media::isVideo($media)) {
                return [$endpoint . "videos", [
                    'description' => $caption,
                    'file_url'    => Media::url($media),
                ]];
            }

            return [null, []];
        }

        // Multiple images carousel
        $mediaIds = [];
        $count    = 0;
        foreach ($medias as $media) {
            if (Media::isImg($media)) {
                $upload = $FB->post($endpoint . 'photos', [
                    'url'       => Media::url($media),
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
}