<?php

namespace Modules\AppChannelLinkedinProfiles\Facades;

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

        if (!in_array($post->type, ['media', 'link', 'text'])) {
            $errors[] = __("LinkedIn API currently supports only 'media', 'link', or 'text' post types.");
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

        $person_id = $linkedin->getPersonID($accessToken);

        $data       = json_decode($post->data, false);
        $medias     = $data->medias ?? [];
        $caption    = spintax($data->caption);
        $comment    = $data->advance_options->linkedin_first_comment ?? '';
        $link       = $data->link ?? '';
        $link_title = $data->advance_options->link_title ?? '';
        $link_desc  = $data->advance_options->link_description ?? '';
        $visibility = "PUBLIC";

        // ─── READ custom thumbnail ─────────────────────────────────────────────
        $customThumb = $data->custom_thumbnail ?? null;
        // ──────────────────────────────────────────────────────────────────────

        switch ($post->type) {
            case 'text':
                $response = $linkedin->linkedInTextPost($accessToken, $person_id, $caption, $visibility);
                break;

            case 'link':
                $response = $linkedin->linkedInLinkPost($accessToken, $person_id, $caption, $link_title, $link_desc, $link, $visibility);
                break;

            case 'media':
                if (count($medias) > 1) {
                    $images = [];
                    foreach ($medias as $media) {
                        $images[] = [
                            'image_path' => watermark($media, $post->account->team_id, $post->account->id),
                            'desc'       => $caption,
                            'title'      => substr($caption, 0, 200),
                        ];
                    }
                    $response = $linkedin->linkedInMultiplePhotosPost($accessToken, $person_id, $caption, $images, $visibility);
                } else {
                    $media = Media::url($medias[0] ?? '');
                    if (!$media) {
                        return self::errorResponse(__("No media provided for single media post."), $post->type);
                    }
                    if (Media::isVideo($media)) {
                        // ─── PASS customThumb ──────────────────────────────────
                        $response = $linkedin->linkedInVideoPost($accessToken, $person_id, $caption, $media, substr($caption, 0, 200), substr($caption, 0, 200), $visibility, $customThumb);
                        // ──────────────────────────────────────────────────────
                    } elseif (Media::isImg($media)) {
                        $image_path = watermark($media, $post->account->team_id, $post->account->id);
                        $response   = $linkedin->linkedInPhotoPost($accessToken, $person_id, $caption, $image_path, substr($caption, 0, 200), substr($caption, 0, 200), $visibility);
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

    protected static function errorResponse($message, $type)
    {
        return [
            "status"  => 0,
            "message" => __($message),
            "type"    => $type,
        ];
    }
}
