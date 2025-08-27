<?php
namespace Modules\AppChannelGBPLocations\Facades;

use Illuminate\Support\Facades\Facade;
use Google\Client;
use Modules\AppChannels\Models\Accounts;
use Media;
use GuzzleHttp\Psr7\Request;

class Post extends Facade
{
    protected static $client;

    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    /**
     * Initialize the Google Business Profile API client.
     *
     * @return Client
     */
    protected static function initGBP()
    {
        self::$client = new Client();
        self::$client->setClientId(get_option("gbp_client_id", ""));
        self::$client->setClientSecret(get_option("gbp_client_secret", ""));
        self::$client->setDeveloperKey(get_option("gbp_api_key", ""));
        self::$client->setApplicationName("Google Business Profile");
        self::$client->setApprovalPrompt("force");
        self::$client->setAccessType('offline');
        self::$client->setScopes([
            'https://www.googleapis.com/auth/business.manage'
        ]);

        return self::$client;
    }

    /**
     * Validate post data.
     *
     * Expected data:
     *  - summary: The text content of the post.
     *  - media_path: (optional) The file path to an image.
     *
     * @param object $post
     * @return array An array of error messages (if any)
     */
    protected static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, false);
        $medias = $data->medias;
        $options = $data->options;

        switch ($post->type) {
            case 'media':
                $media = Media::url($medias[0]);
                if (!Media::isImg($media)) {
                    $errors[] = __( "Google Business Profile: The media file is missing, invalid, or not an image.");
                }
        }

        $actions = ['LEARN_MORE', 'BOOK', 'ORDER', 'SHOP', 'SIGN_UP'];
        if(isset($options->gbp_action) && in_array($options->gbp_action, $actions)){
            if(!isset($options->gbp_link) || $options->gbp_link == ''){
                $errors[] = __( "Google Business Profile: Action link is required for call to action");
            }
        }


        return $errors;
    }

    /**
     * Main method to create a local post on Google Business Profile.
     *
     * It refreshes the token if needed and then sends a raw HTTP POST
     * request to the v4 localPosts endpoint.
     *
     * @param object $post
     * @return array The API response data or an error response.
     */
    protected static function post($post)
    {
        self::initGBP();
        $tokenInfo = json_decode($post->account->token, false);
        self::$client->setAccessToken($post->account->token);

        // Refresh the access token if necessary:
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

        // Validate post data:
        $errors = self::validator($post);
        if (!empty($errors)) {
            return [
                "status"  => "error",
                "message" => $errors,
                "type"    => $post->type,
            ];
        }

        // Decode post data
        $data = json_decode($post->data, false);
        $medias   = $data->medias;
        $options = $data->options;
        $summary = spintax($data->caption);
        $mediaPath = '';

        if(!empty($medias)){
            $media = Media::url($medias[0]);
            if (Media::isImg($medias[0])) {
                $mediaPath = $media;
            }
        }

        $link = $data->link ?? '';
        if($link){
            $summary .= " ".$link;
        }

        $callToAction = '';
        if(isset($options->gbp_action) && isset($options->gbp_link) && $options->gbp_link != ""){
            $callToAction = [ "actionType" => $options->gbp_action, "url" => $options->gbp_link ];
        }
        
        $params = [
            'summary' => $summary,
            'options' => $options,
            'mediaPath' => $mediaPath,
            'callToAction' => $callToAction,
            'link' => $link,
        ];

        // Ensure the parent's resource name is fully qualified,
        // e.g. "accounts/123456789/locations/987654321"
        $parent = $post->account->pid;

        try {
            return self::handleLocalPostUpload($params, $parent, $post->type);
        } catch (\Exception $e) {
            return self::errorResponse($e->getMessage(), $post->type);
        }
    }

    /**
     * Handle sending a raw HTTP request to create a local post.
     *
     * @param string $summary The local post summary.
     * @param string $mediaPath Optional media file path.
     * @param string $parent The resource name (e.g. "accounts/123456789/locations/987654321").
     * @param string $postType The post type.
     * @return array The API response.
     */
    protected static function handleLocalPostUpload($params, $parent, $postType)
    {
        // Use the v4 endpoint:
        $url = "https://mybusiness.googleapis.com/v4/{$parent}/localPosts";

        $payload = [
            "name" => $params['summary'],
            "summary" => $params['summary'],
            "topicType" => 'STANDARD',
        ];

        if($params['callToAction']){
            $payload['callToAction'] = $params['callToAction'];
        }

        $mediaPath = $params['mediaPath'];
        if (!empty($mediaPath)) {
            $payload["media"] = [
                [
                    "mediaFormat" => "PHOTO",
                    "sourceUrl" => $mediaPath
                ]
            ];
        }

        $body = json_encode($payload);
        $httpClient = self::$client->getHttpClient();
        $accessToken = self::$client->getAccessToken()['access_token'];

        $headers = [
            "Content-Type"  => "application/json",
            "Authorization" => "Bearer " . $accessToken,
        ];

        $request = new Request("POST", $url, $headers, $body);
        $response = $httpClient->send($request);
        $responseData = json_decode($response->getBody()->getContents(), true);

        if(isset($responseData['error'])){
            return [
                "status"  => 0,
                "message" => $responseData['error']['details'][0]['errorDetails'][0]['message']
            ];
        }

        return [
            "status"  => 1,
            "message" => __("Success"),
            "id"      => $responseData['name'],
            "url"     => $responseData['searchUrl'],
            "type"    => $postType
        ];
    }

    /**
     * Upload media using the v4 media upload endpoint.
     *
     * @param string $parent The fully qualified resource name.
     * @param string $mediaPath File path to the image.
     * @return string The URL of the uploaded media (or empty string on error).
     */
    protected static function uploadMedia($parent, $mediaPath)
    {
        $url = "https://mybusiness.googleapis.com/v4/{$parent}/media";
        $fileData = file_get_contents($mediaPath);
        $httpClient = self::$client->getHttpClient();
        $accessToken = self::$client->getAccessToken()['access_token'];

        $headers = [
            "Content-Type"  => "application/octet-stream",
            "Authorization" => "Bearer " . $accessToken,
        ];

        $request = new Request("POST", $url, $headers, $fileData);
        $response = $httpClient->send($request);
        $responseData = json_decode($response->getBody()->getContents(), true);

        // Adjust this part based on the actual response structure.
        return isset($responseData["mediaItemData"]["url"]) ? $responseData["mediaItemData"]["url"] : "";
    }

    /**
     * Returns a standardized error response.
     *
     * @param string $message The error message.
     * @param string $type The post type.
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
}
