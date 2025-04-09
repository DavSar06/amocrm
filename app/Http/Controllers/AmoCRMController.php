<?php

namespace App\Http\Controllers;

use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiException;
use Illuminate\Routing\Redirector;
use League\OAuth2\Client\Token\AccessToken;
use Random\RandomException;

class AmoCRMController extends Controller
{
    protected AmoCRMApiClient $apiClient;

    public function __construct()
    {
        $clientId = env('AMOCRM_CLIENT_ID');
        $clientSecret = env('AMOCRM_CLIENT_SECRET');
        $redirectUri = env('AMOCRM_REDIRECT_URI');
        $domain = env('AMOCRM_BASE_DOMAIN').'.amocrm.ru';

        $this->apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
        $this->apiClient->setAccountBaseDomain($domain);
        if(session()->has('TOKEN_FILE')) {
            $this->apiClient->setAccessToken($this->getToken());
            if($this->getToken()->hasExpired()){
                $this->logout();
            }
        }
    }

    /**
     * @return Factory|View|Application|object
     */
    public function index(){
        $accessToken = null;
        if(session()->has('TOKEN_FILE')) {
            $accessToken = json_decode(session('TOKEN_FILE'), true);
        }
        $baseDomain = env('AMOCRM_BASE_DOMAIN').'.amocrm.ru';
        $access = false;
        if(!empty($accessToken)) $access = true;
        return view('home')->with(['oauth2access' => $access, 'baseDomain' => $baseDomain]);
    }

    /**
     * @return RedirectResponse
     */
    public function logout(){
        if(session()->has('TOKEN_FILE')){
            $accessToken = json_decode(session('TOKEN_FILE'), true);
            if(!empty($accessToken)) {
                session()->forget('TOKEN_FILE');
            }
        }
        return redirect()->route('home');
    }

    /**
     * @return Application|object|Redirector|RedirectResponse
     * @throws RandomException
     */
    public function login()
    {
        if(!$this->apiClient->isAccessTokenSet()) {
            $this->apiClient->setAccountBaseDomain('amocrm.ru');
            $state = bin2hex(random_bytes(16));
            session()->put('oauth2state', $state);
            $authorizationUrl = $this->apiClient->getOAuthClient()->getAuthorizeUrl([
                'state' => $state,
                'mode' => 'post_message',
            ]);
            return redirect($authorizationUrl);
        }else return redirect()->back();
    }

    /**
     * @return RedirectResponse|void
     */
    public function callback()
    {
        if(isset($_GET['error'])) {
            abort(403);
        }

        if (isset($_GET['referer'])) {
            $this->apiClient->setAccountBaseDomain($_GET['referer']);
        }

        if(isset($_GET['state'])) {
            if($_GET['state'] == session('oauth2state')){
                try {
                    $accessToken = $this->apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);
                    $this->saveToken([
                        'accessToken' => $accessToken->getToken(),
                        'refreshToken' => $accessToken->getRefreshToken(),
                        'expires' => $accessToken->getExpires(),
                        'baseDomain' => $this->apiClient->getAccountBaseDomain(),
                    ]);
                } catch (AmoCRMApiException $e) {
                    dd($e->getMessage());
//                    return view('error', ['message' => $e->getMessage()]);
                }
                return redirect()->route('home');
            } abort(502);
        }else abort(404);
    }

    /**
     * @return JsonResponse|void
     * @throws AmoCRMMissedTokenException
     */
    public function get_lead()
    {
        if(!$this->apiClient->isAccessTokenSet()) abort(403);
        $leadsService = $this->apiClient->leads();
        $statuses = [
            142 => 'Успешно реализовано',
            143 => 'Закрыто и не реализовано',
            32392165 => 'Принимают решение',
            32392159 => 'Первичный контакт',
            32392156 => 'Неразобранное',
            3177727 => 'Воронка',
        ];
        try {
            $leadsArr = array();
            $i = 0;
            foreach ($leadsService->get() as $lead) {
                $leadsArr[$i] = [
                    'status' => $statuses[$lead->statusId],
                    'name' => $lead->name,
                    'contacts' => $lead->contacts,
                    'updatedAt' => date('Y-m-d H:i:s', $lead->updatedAt),
                ];
            }
            return response()->json($leadsArr);
        } catch (AmoCRMoAuthApiException|AmoCRMApiException $e) {
            abort(404);
        }
    }

    /**
     * @param $accessToken
     * @return void
     */
    protected function saveToken($accessToken)
    {
        if(
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            $data = [
                'accessToken' => $accessToken['accessToken'],
                'expires' => $accessToken['expires'],
                'refreshToken' => $accessToken['refreshToken'],
                'baseDomain' => $accessToken['baseDomain'],
            ];
            session(['TOKEN_FILE' => json_encode($data)]);
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    /**
     * @return AccessToken|void
     */
    protected function getToken()
    {
        if (empty(session('TOKEN_FILE'))) {
            exit('Access token file not found');
        }

        $accessToken = json_decode(session('TOKEN_FILE'), true);

        return new AccessToken([
            'access_token' => $accessToken['accessToken'],
            'refresh_token' => $accessToken['refreshToken'],
            'expires' => $accessToken['expires'],
            'baseDomain' => $accessToken['baseDomain'],
        ]);
    }
}
