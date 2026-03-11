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
                    if (!empty($medias) && Media::isImg($medias[0])) {
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
        $comment = $data->advance_options->x_first_comment ?? '';


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
        $mediaInput = method_exists('Media', 'localPath')
            ? (Media::localPath($media) ?: Media::url($media))
            : Media::url($media);

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
        $mediaIds = [];
        foreach ($medias as $media) {
            $mediaInput = method_exists('Media', 'localPath')
                ? (Media::localPath($media) ?: Media::url($media))
                : Media::url($media);
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
}