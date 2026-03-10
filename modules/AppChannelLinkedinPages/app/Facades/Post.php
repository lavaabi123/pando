<?php

namespace Modules\AppChannelLinkedinPages\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppChannelLinkedinProfiles\Classes\LinkedinAPI;
use Modules\AppChannels\Models\Accounts;
use Media;

class Post extends Facade
{
    private static $linkedin;

    public static function initLinkedin($token = null)
    {
        if (!self::$linkedin) {
            self::$linkedin = new LinkedinAPI(
                get_option("linkedin_app_id", ""),
                get_option("linkedin_app_secret", ""),
                "",
                "",
                false
            );
        }
    }

    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    protected static function validator($post)
    {
        $errors = [];
        $data   = json_decode($post->data, true);
        $medias = $data['medias'] ?? [];

        if (!in_array($post->type, ['media', 'link', 'text', 'video'])) {
            $errors[] = __("LinkedIn API currently supports only 'media', 'link', 'text' or 'video' post types.");
        }

        if (isset($data['advance_options']['linkedin_post_type'])) {
            $postType = $data['advance_options']['linkedin_post_type'];
            if ($postType === 'video' && !empty($medias) && Media::isImg($medias[0])) {
                $errors[] = __("LinkedIn requires a video for the 'video' post type; images are not supported.");
            }
        }

        return $errors;
    }

    protected static function post($post)
    {
        $accessToken = $post->account->token;

        self::initLinkedin($accessToken);
        $linkedin = self::$linkedin;

        if (isset($post->account->category) && $post->account->category === "page") {
            $linkedin->setType("urn:li:organization:");
            $authorId = $post->account->pid;
        } else {
            $authorId = $linkedin->getPersonID($accessToken);
        }

        $data       = json_decode($post->data, false);
        $medias     = $data->medias ?? [];
        $caption    = spintax($data->caption);
        $visibility = "PUBLIC";
        $link       = $data->link ?? '';
        $link_title = $data->advance_options->link_title ?? '';
        $link_desc  = $data->advance_options->link_description ?? '';
        $comment    = $data->advance_options->linkedin_first_comment ?? '';

        // ─── READ custom thumbnail ─────────────────────────────────────────────
        $customThumb = $data->custom_thumbnail ?? null;
        // ──────────────────────────────────────────────────────────────────────

        switch ($post->type) {
            case 'text':
                $response = $linkedin->linkedInTextPost($accessToken, $authorId, $caption, $visibility);
                break;

            case 'link':
                $response = $linkedin->linkedInLinkPost($accessToken, $authorId, $caption, $link_title, $link_desc, $link, $visibility);
                break;

            case 'media':
                if (count($medias) > 1) {
                    $images = [];
                    foreach ($medias as $media) {
                        $images[] = [
                            'image_path' => Media::url($media),
                            'desc'       => $caption,
                            'title'      => substr($caption, 0, 200),
                        ];
                    }
                    $response = $linkedin->linkedInMultiplePhotosPost($accessToken, $authorId, $caption, $images, $visibility);
                } else {
                    $media_url = Media::url($medias[0] ?? '');
                    if (!$media_url) {
                        return self::errorResponse(__("No media provided for single media post."), $post->type);
                    }
                    if (Media::isVideo($media_url)) {
                        // ─── PASS customThumb ────────────────────────────────
                        $response = $linkedin->linkedInVideoPost($accessToken, $authorId, $caption, $media_url, substr($caption, 0, 200), substr($caption, 0, 200), $visibility, $customThumb);
                        // ────────────────────────────────────────────────────
                    } elseif (Media::isImg($media_url)) {
                        $response = $linkedin->linkedInPhotoPost($accessToken, $authorId, $caption, $media_url, substr($caption, 0, 200), substr($caption, 0, 200), $visibility);
                    } else {
                        return self::errorResponse(__("Unsupported media type."), $post->type);
                    }
                }
                break;

            case 'video':
                $media_url = Media::url($medias[0] ?? '');
                if (!$media_url) {
                    return self::errorResponse(__("No media provided for video post."), $post->type);
                }
                if (!Media::isVideo($media_url)) {
                    return self::errorResponse(__("Provided media is not a video."), $post->type);
                }
                // ─── PASS customThumb ──────────────────────────────────────────
                $response = $linkedin->linkedInVideoPost($accessToken, $authorId, $caption, $media_url, substr($caption, 0, 200), substr($caption, 0, 200), $visibility, $customThumb);
                // ──────────────────────────────────────────────────────────────
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

    protected static function errorResponse($message, $type)
    {
        return [
            "status"  => 0,
            "message" => __($message),
            "type"    => $type,
        ];
    }
}
