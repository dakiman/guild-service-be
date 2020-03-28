<?php


namespace App\Services\Blizzard;


use App\Exceptions\BlizzardServiceException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class BlizzardAuthClient
{
    private string $clientId;
    private string $clientSecret;
    private string $oauthUrl;

    public function __construct($region)
    {
        $this->clientId = config('blizzard.client.id');
        $this->clientSecret = config('blizzard.client.secret');
        $this->oauthUrl = str_replace('{region}', $region, config('blizzard.oauth.url'));

        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new BlizzardServiceException('Blizzard client id/secret not found.');
        }
    }

    public function retrieveToken(): string
    {
        $token = cache('token');

        /*If the token is not in the cache, go and retrieve it from blizzard services*/
        if (empty($token)) {
            $client = new Client([
                'base_uri' => $this->oauthUrl,
                'auth' => [$this->clientId, $this->clientSecret],
            ]);

            try {
                $response = $client->post('/oauth/token', [
                    'form_params' => ['grant_type' => 'client_credentials'],
                ]);
            } catch (\Exception $e) {
                throw new BlizzardServiceException('Couldnt retrieve token for communication with Blizzard services.', $e);
            }

            $responseBody = json_decode($response->getBody());

            $token = $responseBody->access_token;
            cache(['token' => $token], now()->addSeconds($responseBody->expires_in - 1000));
        }

        return $token;
    }

    public function retrieveOauthAccessToken(string $authCode, string $redirectUri): string
    {
        $client = new Client([
            'auth' => [$this->clientId, $this->clientSecret],
        ]);

        try {
            $response = $client->post($this->oauthUrl . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                    'code' => $authCode
                ],
            ]);
        } catch (\Exception $e) {
            throw new BlizzardServiceException('Couldnt complete Oauth authorization, please try again later', $e);
        }

        return json_decode($response->getBody())->access_token;
    }

}
