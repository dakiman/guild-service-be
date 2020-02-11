<?php

namespace App\Services\Blizzard;

use App\Exceptions\BlizzardServiceException;
use Exception;
use GuzzleHttp\Client;

class BlizzardAuthClient
{
    private string $client_id;
    private string $client_secret;
    private string $oauth_url;

    public function __construct()
    {
        $this->client_id = config('blizzard.client.id');
        $this->client_secret = config('blizzard.client.secret');
        $this->oauth_url = config('blizzard.oauth.url', null);

        if (empty($this->client_id) ||
            empty($this->client_secret)) {
            throw new BlizzardServiceException('Blizzard client id/secret not found.', 500);
        }

        if (empty($this->oauth_url)) {
            throw new BlizzardServiceException('Blizzard OAuth URL not found.', 500);
        }
    }

    public function retrieveToken(): string
    {
        $token = cache('token');

        /*If the token is not in the cache, go and retrieve it from blizzard services*/
        if (empty($token)) {
            $client = new Client([
                'auth' => [$this->client_id, $this->client_secret],
            ]);

            try {
                $response = $client->post($this->oauth_url, [
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
