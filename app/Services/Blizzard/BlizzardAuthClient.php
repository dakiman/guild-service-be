<?php


namespace App\Services\Blizzard;

use App\Exceptions\BlizzardServiceException;
use GuzzleHttp\Client;

class BlizzardAuthClient
{
    private string $clientId;
    private string $clientSecret;

    public function __construct($clientId, $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new BlizzardServiceException('Blizzard client id/secret not found.');
        }
    }

    /*For now defaulting to EU seems to work for all regions, and is easiest for code structure*/
    public function getToken($region = 'eu'): object
    {
        $client = new Client([
            'base_uri' => str_replace('{region}', $region, config('blizzard.oauth.url')),
            'auth' => [$this->clientId, $this->clientSecret],
        ]);

        try {
            $response = $client->post('/oauth/token', [
                'form_params' => ['grant_type' => 'client_credentials'],
            ]);
        } catch (\Exception $e) {
            throw new BlizzardServiceException('Couldnt retrieve token for communication with Blizzard services.', $e);
        }

        return json_decode($response->getBody());
    }

    public function getOauthAccessToken(string $region, string $authCode, string $redirectUri)
    {
        $client = new Client([
            'base_uri' => str_replace('{region}', $region, config('blizzard.oauth.url')),
            'auth' => [$this->clientId, $this->clientSecret],
        ]);

        try {
            $response = $client->post('/oauth/token', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                    'code' => $authCode
                ],
            ]);
        } catch (\Exception $e) {
            throw new BlizzardServiceException('Couldnt complete Oauth authorization, please try again later', $e);
        }

        return json_decode($response->getBody());
    }

    public function getUserAccountDetails(string $token, string $region)
    {
        $oauthUrl = str_replace('{region}', $region, config('blizzard.oauth.url'));

        $client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ],
            'query' => [
                'namespace' => 'profile-' . $region,
                'locale' => 'en_GB'
            ]
        ]);

        try {
            $response = $client->get($oauthUrl . '/oauth/userinfo');
        } catch (Exception $e) {
            throw new BlizzardServiceException('Had issues finishing OAuth process.', $e, 404);
        }

        return json_decode($response->getBody());
    }




}
