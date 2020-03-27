<?php


namespace App\Services\Blizzard;

use GuzzleHttp\Client;

class BlizzardUserClient
{
    private string $oauthUrl;

    public function __construct($locale)
    {
        $this->oauthUrl = str_replace('{locale}', $locale, config('blizzard.oauth.url'));
//        $this->oauthUrl = str_replace('{locale}', $locale, config('blizzard.api.url'));
    }

    public function getUserInfo($token)
    {
        $client = new Client([
            'base_uri' => $this->oauthUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        $response = $client->get('/oauth/userinfo');
//        $response = $client->get('/profile/user/wow?access_token=' . $token);

        return json_decode($response->getBody());
    }


}
