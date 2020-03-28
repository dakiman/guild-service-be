<?php


namespace App\Services;

use App\Services\Blizzard\BlizzardAuthClient;

class BlizzardAuthService
{
    private BlizzardAuthClient $blizzardAuthClient;

    public function __construct(BlizzardAuthClient $blizzardAuthClient)
    {
        $this->blizzardAuthClient = $blizzardAuthClient;
    }

    public function refreshAndCacheAccessToken()
    {
        $authResponse = $this->blizzardAuthClient->getToken();

        $token = $authResponse->access_token;

        cache(['token' => $token], now()->addSeconds($authResponse->expires_in - 1000));

        return $token;
    }

    public function syncBattleNetDetailsAndGetToken(string $region, string $code, string $redirectUri)
    {
        $authResponse = $this->blizzardAuthClient->getOauthAccessToken($region, $code, $redirectUri);

        $token = $authResponse->access_token;

        $this->saveOauthDetails($token, $region);

        return $token;
    }

    private function saveOauthDetails($token, $region)
    {
        $blizzardOauthData = $this->blizzardAuthClient->getUserAccountDetails($token, $region);

        $user = auth()->user();
        $user->blizzard_id = $blizzardOauthData->id;
        $user->battle_tag = $blizzardOauthData->battletag;
        $user->save();
    }

}
