<?php

namespace App\Services\Blizzard;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;

class BlizzardAuthClient
{

    public function retrieveToken(): string
    {
        $token = cache('token');

        if (empty($token)) {
            $client = new Client([
                'auth' => [env('BLIZZARD_CLIENT_ID'), env('BLIZZARD_CLIENT_SECRET')],
            ]);

            $response = $client->post('https://us.battle.net/oauth/token', [
                'form_params' => ['grant_type' => 'client_credentials'],
            ]);

            $responseBody = json_decode($response->getBody());

            $token = $responseBody->access_token;
            cache(['token' => $token], now()->addSeconds($responseBody->expires_in));
        }

        return $token;
    }

}
