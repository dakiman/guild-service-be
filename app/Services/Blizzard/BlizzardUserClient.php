<?php


namespace App\Services\Blizzard;

use GuzzleHttp\Client;

class BlizzardUserClient
{
    private string $oauthUrl;

    public function __construct($locale)
    {
        $this->oauthUrl = str_replace('{locale}', $locale, config('blizzard.oauth.url'));
    }

    public function getUserInfo($token)
    {
        $client = new Client([
            'base_uri' => $this->oauthUrl,
        ]);

        $response = $client->get('/oauth/userinfo');
        dd($response);
    }


}
