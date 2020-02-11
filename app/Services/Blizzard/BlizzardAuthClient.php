<?php

namespace App\Services\Blizzard;

use App\Exceptions\BlizzardServiceException;
use Exception;
use GuzzleHttp\Client;

class BlizzardAuthClient
{
    private string $clientId;
    private string $clientSecret;
    private string $oauthUrl;

    public function __construct()
    {
        $this->clientId = config('blizzard.client.id');
        $this->clientSecret = config('blizzard.client.secret');
        $this->oauthUrl = config('blizzard.oauth.url');

        if (empty($this->clientId) ||
            empty($this->clientSecret)) {
            throw new BlizzardServiceException('Blizzard client id/secret not found.', 500);
        }

        if (empty($this->oauthUrl)) {
            throw new BlizzardServiceException('Blizzard OAuth URL not found.', 500);
        }
    }

    public function retrieveToken(): string
    {
        $token = cache('token');

        /*If the token is not in the cache, go and retrieve it from blizzard services*/
        if (empty($token)) {
            $client = new Client([
                'auth' => [$this->clientId, $this->clientSecret],
            ]);

            try {
                $response = $client->post($this->oauthUrl, [
                    'form_params' => ['grant_type' => 'client_credentials'],
                ]);
            } catch (Exception $e) {
                throw new BlizzardServiceException('Couldnt retrieve token for communication with Blizzard services.', 500);
            }

            $responseBody = json_decode($response->getBody());

            $token = $responseBody->access_token;
            cache(['token' => $token], now()->addSeconds($responseBody->expires_in));
        }

        return $token;
    }

}
