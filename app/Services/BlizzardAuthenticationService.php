<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;

class BlizzardAuthenticationService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://us.battle.net/oauth/token',
            'auth' => [env('BLIZZARD_CLIENT_ID'), env('BLIZZARD_CLIENT_SECRET')],
        ]);
    }

    public function retrieveToken(): string
    {
        $token = cache('token');

        if (empty($token)) {
            $response = $this->client->post('', [
                'form_params' => ['grant_type' => 'client_credentials'],
            ]);
            $responseBody = json_decode($response->getBody());
            $token = $responseBody->access_token;
            cache(['token' => $token], now()->addSeconds($responseBody->expires_in));
        }

        return $token;
    }
}
