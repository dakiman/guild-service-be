<?php


namespace App\Services;


use App\Services\Blizzard\BlizzardAuthClient;
use App\Services\Blizzard\BlizzardUserClient;

class BlizzardAuthService
{
    private BlizzardAuthClient $blizzardAuthClient;
    private BlizzardUserClient $blizzardUserClient;

    public function retrieveBlizzardAccountDetails(string $authCode, string $redirectUri, string $locale)
    {
        // call auth client to get oauth token
        $this->initClient($locale);
        $token = $this->blizzardAuthClient->retrieveOauthAccessToken($authCode, $redirectUri);
        // call ? client with token to get user data and characters
        $responses = $this->blizzardUserClient->getUserInfoAndCharacters($token, $locale);
        // save user data & characters
        $this->saveBlizzardDetailsFromResponse($responses['oauth']);
    }

    private function initClient($locale)
    {
        $this->blizzardAuthClient = app(BlizzardAuthClient::class, ['locale' => $locale]);
        $this->blizzardUserClient = app(BlizzardUserClient::class, ['locale' => $locale]);
    }

    private function saveBlizzardDetailsFromResponse($blizzardOauthData)
    {
        $blizzardOauthData = json_decode($blizzardOauthData->getBody());

        $user = auth()->user();
        $user->blizzard_id = $blizzardOauthData->id;
        $user->battle_tag = $blizzardOauthData->battletag;
        $user->save();
    }


}
