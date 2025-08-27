<?php

namespace Modules\AppChannelGBPLocations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\MyBusinessAccountManagement;
use Google\Service\MyBusinessBusinessInformation;
use Google\Service\Oauth2;

class AppChannelGBPLocationsController extends Controller
{
    public $gbp_management;
    public $gbp_information;
    protected $client;
    protected $client_id;
    protected $client_secret;
    protected $api_key;
    protected $callback_url;

    public function __construct()
    {
        \Access::check('appchannels.' . module('key'));

        $this->client_id = get_option("gbp_client_id", "");
        $this->client_secret = get_option("gbp_client_secret", "");
        $this->api_key = get_option("gbp_api_key", "");
        $this->callback_url = module_url();

        if (!$this->client_id || !$this->client_secret || !$this->api_key) {
            \Access::deny(__('To use Google Business Profile, you must first configure the client ID, client secret, and API key.'));
        }

        try {
            $this->client = new \Google_Client();
            $this->client->setApprovalPrompt("force");
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
            $this->client->setApplicationName("GBP");
            $this->client->setClientId($this->client_id);
            $this->client->setClientSecret($this->client_secret);
            $this->client->setRedirectUri($this->callback_url);
            $this->client->setDeveloperKey($this->api_key);
            $this->client->setScopes([
                'https://www.googleapis.com/auth/plus.business.manage',
                'https://www.googleapis.com/auth/business.manage',
                'https://www.googleapis.com/auth/userinfo.email'
            ]);

            $this->client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

            $this->gbp_management = new \Google_Service_MyBusinessAccountManagement($this->client);
            $this->gbp_information = new \Google_Service_MyBusinessBusinessInformation($this->client);

        } catch (\Exception $e) {
            \Log::error('Google My Business SDK init error', ['error' => $e->getMessage()]);
            \Access::deny(__('Could not connect to Google Business API: ') . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $result = [];

        try 
        {
            if( !session("GBP_AccessToken") )
            {
                if(!$request->code)
                {
                    return redirect( module_url("oauth") );
                }

                $accessToken = $this->client->fetchAccessTokenWithAuthCode($request->code);
                session(["GBP_AccessToken" => $accessToken]);
                return redirect( $this->callback_url );
            }
            else
            {
                $accessToken = session("GBP_AccessToken"); 
            }

            $this->client->setAccessToken($accessToken);


            //dd($this->gbp);
            $accountsList = $this->gbp_management->accounts->listAccounts()->getAccounts();
            if(!empty($accountsList))
            {
                $response = [];
                $optional_params = [];
                $optional_params['pageSize'] = 100;
                $optional_params['readMask'] = ['name', 'title', 'storefrontAddress', 'latlng', 'phoneNumbers', 'Metadata'];
                $response = $this->gbp_information->accounts_locations->listAccountsLocations($accountsList[0]->name,$optional_params)->getLocations(); 

                if(!empty($response))
                {
                    foreach ($response as $value) 
                    {
                        $avatar = text2img( $value->getTitle(), 'rand' );

                        $result[] = [
                            'id' => $accountsList[0]->name."/".$value->getName(),
                            'name' => $value->getTitle(),
                            'avatar' => $avatar,
                            'desc' => __("Location"),
                            'link' => $value->getMetaData()->getMapsUri(),
                            'oauth' => $accessToken,
                            'module' => $request->module['module_name'],
                            'reconnect_url' => $request->module['uri']."/oauth",
                            'social_network' => 'google_business_profile',
                            'category' => 'location',
                            'login_type' => 1,
                            'can_post' => 1,
                            'data' => "",
                            'proxy' => 0
                        ];
                    }

                    $channels = [
                        'status' => 1,
                        'message' => __("Succeeded")
                    ];
                }
                else
                {
                    $channels = [
                        'status' => 0,
                        'message' => __('No profile to add'),
                    ];
                }
            }else{
                $channels = [
                    'status' => 0,
                    'message' => __('No profile to add'),
                ];
            }
        } 
        catch (\Exception $e) 
        {
            $channels = [
                'status' => 0,
                'message' => $e->getMessage(),
            ];
        }

        $channels = array_merge($channels, [
            'channels' => $result,
            'module' => $request->module,
            'save_url' => url_app('channels/save'),
            'reconnect_url' => module_url('oauth'),
            'oauth' => session("GBP_AccessToken")
        ]);

        session( ['channels' => $channels] );
        return redirect( url_app("channels/add") );
    }

    public function oauth(Request $request)
    {
        $request->session()->forget('GBP_AccessToken');
        $login_url = $this->client->createAuthUrl();
        return redirect($login_url);
    }

    public function settings(){
        return view('appchannelgbplocations::settings');
    }
}
