<?php

namespace Modules\AppChannelLinkedinProfiles\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Modules\AppChannelLinkedinProfiles\Jobs\LinkedInFirstCommentJob;

class LinkedinAPI  
{
    protected $app_id;
    protected $app_secret;
    protected $callback;
    protected $csrf;
    protected $scopes;
    protected $ssl;
    protected $type;
    protected $client; // Guzzle client

    /**
     * Constructor for LinkedinAPI.
     *
     * @param string $app_id      The application ID.
     * @param string $app_secret  The application secret.
     * @param string $callback    The callback URL.
     * @param string $scopes      The OAuth scopes.
     * @param bool   $ssl         Whether to verify SSL (default: true).
     */
    public function __construct(
        string $app_id, 
        string $app_secret, 
        string $callback, 
        string $scopes, 
        bool $ssl = true
    ) {
        $this->app_id     = $app_id;
        $this->app_secret = $app_secret;
        $this->callback   = $callback;
        $this->scopes     = $scopes;
        $this->ssl        = $ssl;
        $this->csrf       = random_int(1111111111, 99999999999);
        $this->type       = "urn:li:person:";

        // Initialize Guzzle client with SSL verification option.
        $this->client = new Client([
            'verify' => $this->ssl,
            'timeout' => 30
        ]);
    }

    /**
     * Generates the LinkedIn authorization URL.
     *
     * @return string The authorization URL.
     */
    public function getAuthUrl()
    {
        $_SESSION['linkedincsrf'] = $this->csrf;
        return "https://www.linkedin.com/oauth/v2/authorization?response_type=code"
            . "&client_id=" . $this->app_id
            . "&redirect_uri=" . $this->callback
            . "&state=" . $this->csrf
            . "&scope=" . $this->scopes;
    }

    /**
     * Retrieves the access token using the provided authorization code.
     *
     * @param string $code The authorization code.
     * @return array Returns an array with status and either the access token or an error message.
     */
    public function getAccessToken($code)
    {
        $url = "https://www.linkedin.com/oauth/v2/accessToken";
        $params = [
            'client_id'     => $this->app_id,
            'client_secret' => $this->app_secret,
            'redirect_uri'  => $this->callback,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ];

        $options = [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => $params
        ];

        $response = $this->sendRequest('POST', $url, $options);
        $data = json_decode($response);
        if (isset($data->access_token)) {
            return [ "status" => "success", "accessToken" => $data->access_token ];
        } else {
            return [ "status" => "error", "message" => $data->error_description ?? 'Unknown error' ];
        }
    }

    /**
     * Retrieves the entire person information.
     *
     * @param string $accessToken The LinkedIn access token.
     * @return array The person data as associative array.
     */
    public function getPerson($accessToken)
    {
        $url = "https://api.linkedin.com/v2/userinfo?oauth2_access_token=" . $accessToken;
        $response = $this->sendRequest('GET', $url);
        return json_decode($response, true);
    }

    /**
     * Retrieves the LinkedIn person ID.
     *
     * @param string $accessToken The LinkedIn access token.
     * @return mixed The person's ID.
     */
    public function getPersonID($accessToken)
    {
        $url = "https://api.linkedin.com/v2/userinfo?oauth2_access_token=" . $accessToken;
        $response = $this->sendRequest('GET', $url);

        $data = json_decode($response);
        return $data->sub ?? null;
    }

    /**
     * Retrieves company pages for which the user is an administrator.
     *
     * @param string $accessToken The LinkedIn access token.
     * @return array The company pages data as associative array.
     */
    public function getCompanyPages($accessToken)
    {
        $url = "https://api.linkedin.com/v2/organizationalEntityAcls?q=roleAssignee&role=ADMINISTRATOR"
             . "&projection=(elements*(organizationalTarget~(id,localizedName,vanityName,logoV2(original~:playableStreams,cropped~:playableStreams,cropInfo))))"
             . "&oauth2_access_token=" . trim($accessToken);
        $response = $this->sendRequest('GET', $url, [
            'headers' => ['Content-Type' => 'application/json']
        ]);
        return json_decode($response, true);
    }

    /**
     * Sets the type prefix for the author (e.g., "urn:li:person:").
     *
     * @param string $type The new type prefix.
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Posts a text-only update to LinkedIn.
     *
     * @param string $accessToken The LinkedIn access token.
     * @param string $person_id   The person's ID.
     * @param string $message     The post message.
     * @param string $visibility  Post visibility ("PUBLIC" or other).
     * @return mixed The response from LinkedIn.
     */
    public function linkedInTextPost($accessToken, $person_id, $message, $visibility = "PUBLIC", $first_comment = "")
    {
        $url = "https://api.linkedin.com/v2/ugcPosts?oauth2_access_token=" . $accessToken;
        $request = [
            "author"         => $this->type . $person_id,
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary"    => [ "text" => $message ],
                    "shareMediaCategory" => "NONE",
                ]
            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => $visibility,
            ]
        ];
        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($request)
        ];

        $response = $this->sendRequest('POST', $url, $options);

        $responseData = is_string($response) ? json_decode($response, true) : (array)$response;
        $this->postLinkedInFirstComment($accessToken, $person_id, $responseData['id'] ?? null, $first_comment);

        return $response;
    }

    /**
     * Posts a link update to LinkedIn.
     *
     * @param string $accessToken The LinkedIn access token.
     * @param string $person_id   The person's ID.
     * @param string $message     The post message.
     * @param string $link_title  The title of the link.
     * @param string $link_desc   The description of the link.
     * @param string $link_url    The link URL.
     * @param string $visibility  Post visibility (default: "PUBLIC").
     * @return mixed The response from LinkedIn.
     */
    public function linkedInLinkPost($accessToken, $person_id, $message, $link_title, $link_desc, $link_url, $visibility = "PUBLIC", $first_comment = "")
    {
        $url = "https://api.linkedin.com/v2/ugcPosts?oauth2_access_token=" . $accessToken;
        $request = [
            "author"         => $this->type . $person_id,
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary"    => [ "text" => $message ],
                    "shareMediaCategory" => "ARTICLE",
                    "media" => [[
                        "status"      => "READY",
                        "description" => [ "text" => substr($link_desc, 0, 200) ],
                        "originalUrl" => $link_url,
                        "title"       => [ "text" => $link_title ]
                    ]]
                ]
            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => $visibility,
            ]
        ];
        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($request)
        ];

        $response = $this->sendRequest('POST', $url, $options);

        $responseData = is_string($response) ? json_decode($response, true) : (array)$response;
        $this->postLinkedInFirstComment($accessToken, $person_id, $responseData['id'] ?? null, $first_comment);


        return $response;
    }

    /**
     * Posts a video update to LinkedIn.
     *
     * Registers an upload session with the video recipe, uploads the video using the provided
     * video file path, and then creates the post with the video.
     *
     * @param string $accessToken       The LinkedIn access token.
     * @param string $person_id         The person's ID.
     * @param string $message           The post message.
     * @param string $video_path        The local path to the video file.
     * @param string $video_title       The title for the video.
     * @param string $video_description The description of the video.
     * @param string $visibility        Post visibility (default: "PUBLIC").
     * @return mixed The response from LinkedIn.
     */
    public function linkedInVideoPost(
        $accessToken,
        $person_id,
        $message,
        $video_path,
        $video_title,
        $video_description,
        $visibility = "PUBLIC",
        $thumbnailUrl = null,
        $first_comment = ""   
    ) {
        $prepareUrl = "https://api.linkedin.com/v2/assets?action=registerUpload&oauth2_access_token=" . $accessToken;
        $prepareRequest = [
            "registerUploadRequest" => [
                "recipes" => [ "urn:li:digitalmediaRecipe:feedshare-video" ],
                "owner"   => $this->type . $person_id,
                "serviceRelationships" => [
                    [
                        "relationshipType" => "OWNER",
                        "identifier"       => "urn:li:userGeneratedContent"
                    ]
                ]
            ]
        ];
        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($prepareRequest)
        ];
        $prepareResponse = $this->sendRequest('POST', $prepareUrl, $options);
        $prepareData = json_decode($prepareResponse);

        if (!isset($prepareData->message)) {
            $uploadURL = $prepareData->value->uploadMechanism
                ->{"com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest"}
                ->uploadUrl;
            $asset_id = $prepareData->value->asset;

            try {
                $_liVidTmp  = null;
                $fileStream = $this->openMediaStream($video_path, $_liVidTmp);
                if (!$fileStream) throw new \RuntimeException("Could not open video for upload: $video_path");
                $this->client->request('PUT', $uploadURL, [
                    'headers' => [ 'Authorization' => 'Bearer ' . $accessToken ],
                    'body'    => $fileStream
                ]);
                if ($_liVidTmp) @unlink($_liVidTmp);
            } catch (RequestException $e) {
                return json_encode([
                    "error"   => "Video upload failed.",
                    "details" => $e->getMessage()
                ]);
            }

            $parse_id = explode(":", $asset_id);
            $id = end($parse_id);

            $maxPoll = 15; $pollTry = 0; $checkUpload = null;
            do {
                if ($pollTry > 0) sleep(4);
                $checkRaw    = $this->sendRequest('GET', "https://api.linkedin.com/v2/assets/" . $id . "?oauth2_access_token=" . $accessToken);
                $checkUpload = json_decode($checkRaw);
                $pollTry++;
            } while (
                isset($checkUpload->status) &&
                $checkUpload->status !== "ALLOWED" &&
                $checkUpload->status !== "PROCESSING_FAILED" &&
                $pollTry < $maxPoll
            );

            if (!isset($checkUpload->status) || $checkUpload->status === "PROCESSING_FAILED") {
                return json_encode(["error" => "Video processing failed on LinkedIn."]);
            }

            if ($checkUpload->status == "ALLOWED") {

                $mediaEntry = ["status" => "READY", "media" => $asset_id];
                if ($thumbnailUrl) {
                    $mediaEntry["thumbnailUrl"] = $thumbnailUrl;
                }

                $url = "https://api.linkedin.com/v2/ugcPosts?oauth2_access_token=" . $accessToken;
                $request = [
                    "author"          => $this->type . $person_id,
                    "lifecycleState"  => "PUBLISHED",
                    "specificContent" => [
                        "com.linkedin.ugc.ShareContent" => [
                            "shareCommentary"    => ["text" => $message],
                            "shareMediaCategory" => "VIDEO",
                            "media"              => [$mediaEntry],
                        ]
                    ],
                    "visibility" => [
                        "com.linkedin.ugc.MemberNetworkVisibility" => $visibility
                    ]
                ];
                $options = [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body'    => json_encode($request)
                ];

                // CAPTURE response instead of returning directly
                $response = $this->sendRequest('POST', $url, $options);                

                // Post first comment if provided
                $responseData = is_string($response) ? json_decode($response, true) : (array)$response;
                $this->postLinkedInFirstComment($accessToken, $person_id, $responseData['id'] ?? null, $first_comment);
                return $response;
            }

            return json_encode([
                "error"   => "Video upload failed.",
                "details" => "Asset not in ALLOWED state after upload."
            ]);
        } else {
            return $prepareResponse;
        }
    }

    /**
     * Posts a single photo update to LinkedIn.
     *
     * @param string $accessToken       The LinkedIn access token.
     * @param string $person_id         The person's ID.
     * @param string $message           The post message.
     * @param string $image_path        The local path to the image.
     * @param string $image_title       The title for the image.
     * @param string $image_description The image description.
     * @param string $visibility        Post visibility (default: "PUBLIC").
     * @return mixed The response from LinkedIn.
     */
    public function linkedInPhotoPost($accessToken, $person_id, $message, $image_path, $image_title, $image_description, $visibility = "PUBLIC", $first_comment = "")
    {
        $prepareUrl = "https://api.linkedin.com/v2/assets?action=registerUpload&oauth2_access_token=" . $accessToken;
        $prepareRequest = [
            "registerUploadRequest" => [
                "recipes" => ["urn:li:digitalmediaRecipe:feedshare-image"],
                "owner"   => $this->type . $person_id,
                "serviceRelationships" => [
                    [
                        "relationshipType" => "OWNER",
                        "identifier"       => "urn:li:userGeneratedContent"
                    ]
                ]
            ]
        ];
        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($prepareRequest)
        ];
        $prepareResponse = $this->sendRequest('POST', $prepareUrl, $options);
        $prepareData = json_decode($prepareResponse);

        if (!isset($prepareData->message)) {
            $uploadURL = $prepareData->value->uploadMechanism
                ->{"com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest"}
                ->uploadUrl;
            $asset_id = $prepareData->value->asset;

            try {
                $_liImgTmp  = null;
                $fileStream = $this->openMediaStream($image_path, $_liImgTmp);
                if (!$fileStream) throw new \RuntimeException("Could not open image for upload: $image_path");
                $this->client->request('PUT', $uploadURL, [
                    'headers' => [ 'Authorization' => 'Bearer ' . $accessToken ],
                    'body'    => $fileStream
                ]);
                if ($_liImgTmp) @unlink($_liImgTmp);
            } catch (RequestException $e) {
                return json_encode(["error" => "Image upload failed.", "details" => $e->getMessage()]);
            }

            $url = "https://api.linkedin.com/v2/ugcPosts?oauth2_access_token=" . $accessToken;
            $request = [
                "author"         => $this->type . $person_id,
                "lifecycleState" => "PUBLISHED",
                "specificContent" => [
                    "com.linkedin.ugc.ShareContent" => [
                        "shareCommentary"    => [ "text" => $message ],
                        "shareMediaCategory" => "IMAGE",
                        "media" => [[
                            "status"      => "READY",
                            "description" => [ "text" => substr($image_description, 0, 200) ],
                            "media"       => $asset_id,
                            "title"       => [ "text" => $image_title ]
                        ]]
                    ]
                ],
                "visibility" => [
                    "com.linkedin.ugc.MemberNetworkVisibility" => $visibility,
                ]
            ];
            $options = [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode($request)
            ];

            $response = $this->sendRequest('POST', $url, $options);

            $responseData = is_string($response) ? json_decode($response, true) : (array)$response;
            $this->postLinkedInFirstComment($accessToken, $person_id, $responseData['id'] ?? null, $first_comment);

            return $response;

        } else {
            return $prepareResponse;
        }
    }

    /**
     * Posts multiple photos to LinkedIn.
     *
     * @param string $accessToken The LinkedIn access token.
     * @param string $person_id   The person's ID.
     * @param string $message     The post message.
     * @param array  $images      An array of image information objects (each with keys: image_path, desc, title).
     * @param string $visibility  Post visibility (default: "PUBLIC").
     * @return mixed The response from LinkedIn.
     */
    public function linkedInMultiplePhotosPost($accessToken, $person_id, $message, array $images, $visibility = "PUBLIC", $first_comment = "")
    {
        $media = [];
        foreach ($images as $key => $image) {
            $prepareUrl = "https://api.linkedin.com/v2/assets?action=registerUpload&oauth2_access_token=" . $accessToken;
            $prepareRequest = [
                "registerUploadRequest" => [
                    "recipes" => ["urn:li:digitalmediaRecipe:feedshare-image"],
                    "owner"   => $this->type . $person_id,
                    "serviceRelationships" => [
                        [
                            "relationshipType" => "OWNER",
                            "identifier"       => "urn:li:userGeneratedContent"
                        ]
                    ]
                ]
            ];
            $options = [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode($prepareRequest)
            ];
            $prepareResponse  = $this->sendRequest('POST', $prepareUrl, $options);
            $prepareData = json_decode($prepareResponse);
            if (!isset($prepareData->message)) {
                $uploadURL = $prepareData->value->uploadMechanism
                    ->{"com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest"}
                    ->uploadUrl;
                $asset_id = $prepareData->value->asset;
                $images[$key]['asset_id'] = $asset_id;
                try {
                    $_liMulTmp  = null;
                    $fileStream = $this->openMediaStream($image['image_path'], $_liMulTmp);
                    if (!$fileStream) throw new \RuntimeException("Could not open image for upload: {$image['image_path']}");
                    $this->client->request('PUT', $uploadURL, [
                        'headers' => [ 'Authorization' => 'Bearer ' . $accessToken ],
                        'body'    => $fileStream
                    ]);
                    if ($_liMulTmp) @unlink($_liMulTmp);
                } catch (RequestException $e) {
                    return json_encode(["error" => "Image upload failed.", "details" => $e->getMessage()]);
                }
                $media[$key]["status"] = "READY";
                $media[$key]["description"]["text"] = substr($image["desc"], 0, 200);
                $media[$key]["media"] = $asset_id;
                $media[$key]["title"]["text"] = substr($image["title"], 0, 200);
            } else {
                return $prepareResponse;
            }
        }

        // Publish the post
        $url = "https://api.linkedin.com/v2/ugcPosts?oauth2_access_token=" . $accessToken;
        $request = [
            "author"         => $this->type . $person_id,
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary"    => [ "text" => $message ],
                    "shareMediaCategory" => "IMAGE",
                    "media"              => array_values($media)
                ]
            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => $visibility,
            ]
        ];
        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($request)
        ];
        $response = $this->sendRequest('POST', $url, $options);
        // Post first comment if provided
        $responseData = is_string($response) ? json_decode($response, true) : (array)$response;
        $this->postLinkedInFirstComment($accessToken, $person_id, $responseData['id'] ?? null, $first_comment);

        return $response;
    }

    /**
     * A helper method that sends HTTP requests using Guzzle.
     *
     * @param string $method  The HTTP method (GET, POST, PUT, etc.).
     * @param string $url     The target URL.
     * @param array  $options Optional Guzzle options.
     * @return string The response body.
     */
    private function sendRequest($method, $url, $options = [])
    {
        try {
            $response = $this->client->request($method, $url, $options);
            return (string)$response->getBody();
        } catch (RequestException $e) {
            // Return the response body if available, or the error message.
            if ($e->hasResponse()) {
                return (string)$e->getResponse()->getBody();
            }
            return $e->getMessage();
        }
    }
		
	public function linkedInCommentGet($accessToken, $personId)
	{
		$url = 'https://api.linkedin.com/rest/organizationalEntityNotifications?'
			 . 'q=criteria'
			 . '&actions[0]=COMMENT'
			 . '&actions[1]=SHARE_MENTION'
			 . '&organizationalEntity=urn%3Ali%3Aorganization%3A' . $personId
			 . '&count=100';
		
		$options = [
			'headers' => [
				'Authorization' => 'Bearer ' . trim($accessToken),
				'LinkedIn-Version' => '202601'
			]
		];
		
		return $this->sendRequest('GET', $url, $options);
	}


public function linkedInCommentDetailGet($accessToken, $activityId)
{
    $projection = '(elements(*,actor~(localizedFirstName,localizedLastName,profilePicture(displayImage~:playableStreams))))';
    
    $url = 'https://api.linkedin.com/v2/socialActions/' 
         . urlencode($activityId) 
         . '/comments?projection=' . urlencode($projection);
    
    $options = [
        'headers' => [
            'Authorization' => 'Bearer ' . trim($accessToken),
            'Content-Type' => 'application/json'
        ]
    ];
    
    return $this->sendRequest('GET', $url, $options);
}


public function linkedInCommentPost($accessToken, $personId, $message, $activityUrn)
{
	
    $activityUrn = trim($activityUrn);
	
	if (strpos($activityUrn, 'urn:li:activity:') === 0) {
        $activityId = str_replace('urn:li:activity:', '', $activityUrn);
        $activityUrn = 'urn:li:share:' . $activityId;
    }

    $url = 'https://api.linkedin.com/rest/socialActions/'
         . rawurlencode($activityUrn)
         . '/comments';

    $request = [
        "actor"   => "urn:li:organization:" . $personId,
        "message" => [ "text" => $message ],
    ];

    $options = [
        'headers' => [
            'Authorization' => 'Bearer ' . trim($accessToken),
            'Linkedin-Version' => '202601',
            'X-Restli-Protocol-Version' => '2.0.0',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'body' => json_encode($request),
    ];

    return $this->sendRequest('POST', $url, $options);
}

public function linkedInPostDetailGet($accessToken, $postUrn)
{
    $url = 'https://api.linkedin.com/rest/posts/' . urlencode($postUrn);
    
    $options = [
        'headers' => [
            'Authorization' => 'Bearer ' . trim($accessToken),
            'LinkedIn-Version' => '202601'
        ]
    ];
    
    return $this->sendRequest('GET', $url, $options);
}


    /**
     * Resolve a media path/URL to a readable PHP stream.
     *
     * Callers pass whatever the Post facade passes — which after our fixes is
     * always a full public URL.  fopen(URL, 'r') is unreliable (requires
     * allow_url_fopen, streams headers, fails on large files).
     *
     * Strategy:
     *   1. If it is a local absolute path → fopen directly.
     *   2. If it is a URL → try to map to local storage path (zero-copy).
     *   3. Otherwise  → download to a temp file via curl and open that.
     *
     * @param  string  $pathOrUrl
     * @param  ?string &$tmpFile   Set to temp file path if one was created (caller must unlink)
     * @return resource|false
     */
    private function openMediaStream(string $pathOrUrl, ?string &$tmpFile = null)
    {
        $tmpFile = null;

        // Already a local absolute path
        if (!filter_var($pathOrUrl, FILTER_VALIDATE_URL)) {
            return file_exists($pathOrUrl) ? fopen($pathOrUrl, 'r') : false;
        }

        // Try to map URL → local filesystem path
        $urlPath  = parse_url($pathOrUrl, PHP_URL_PATH) ?: '';
        $prefixes = ['/storage/app/public/', '/storage/'];
        foreach ($prefixes as $pfx) {
            if (str_starts_with($urlPath, $pfx)) {
                $rel = substr($urlPath, strlen($pfx));
                $candidates = [
                    storage_path('app/public/' . $rel),
                    public_path('storage/' . $rel),
                    storage_path('app/' . $rel),
                    base_path('storage/app/public/' . $rel),
                ];
                foreach ($candidates as $c) {
                    if (file_exists($c) && filesize($c) > 0) {
                        return fopen($c, 'r');
                    }
                }
            }
        }

        // Fall back: stream-download via curl to a temp file
        $ext  = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION)) ?: 'bin';
        $tmp  = tempnam(sys_get_temp_dir(), 'li_media_') . ".$ext";
        $fh   = fopen($tmp, 'wb');
        if (!$fh) return false;

        $ch = curl_init($pathOrUrl);
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fh,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 600,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fh);

        if ($code === 200 && file_exists($tmp) && filesize($tmp) > 0) {
            $tmpFile = $tmp;
            return fopen($tmp, 'r');
        }
        @unlink($tmp);
        return false;
    }

    private function postLinkedInFirstComment($accessToken, $person_id, $postUrn, $first_comment)
    {
        if (empty($first_comment) || empty($postUrn)) return;

        LinkedInFirstCommentJob::dispatch(
            $accessToken,
            $person_id,
            $this->type,
            $postUrn,
            $first_comment
        )->delay(now()->addSeconds(5)); // small delay before job starts
    }

}