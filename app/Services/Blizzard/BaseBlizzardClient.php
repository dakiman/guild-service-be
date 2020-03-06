<?php


namespace App\Services\Blizzard;


use App\Exceptions\BlizzardServiceException;
use GuzzleHttp\Client;

class BaseBlizzardClient
{

    protected function retrieveToken(): string
    {
        $clientId = config('blizzard.client.id');
        $clientSecret = config('blizzard.client.secret');
        $oauthUrl = config('blizzard.oauth.url');

        if (empty($clientId) || empty($clientSecret)) {
            throw new BlizzardServiceException('Blizzard client id/secret not found.');
        }

        $token = cache('token');

        /*If the token is not in the cache, go and retrieve it from blizzard services*/
        if (empty($token)) {
            $client = new Client([
                'auth' => [$clientId, $clientSecret],
            ]);

            try {
                $response = $client->post($oauthUrl, [
                    'form_params' => ['grant_type' => 'client_credentials'],
                ]);
            } catch (Exception $e) {
                throw new BlizzardServiceException('Couldnt retrieve token for communication with Blizzard services.', $e);
            }

            $responseBody = json_decode($response->getBody());

            $token = $responseBody->access_token;
            cache(['token' => $token], now()->addSeconds($responseBody->expires_in));
        }

        return $token;
    }

}
