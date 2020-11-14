<?php


namespace App\Services;

use App\Exceptions\ApiException;
use App\Services\Blizzard\BlizzardAuthClient;
use App\Services\Blizzard\BlizzardUserClient;
use GuzzleHttp\Psr7\Response;
use Log;

class BlizzardAuthService
{
    private BlizzardAuthClient $authClient;
    private BlizzardUserClient $userClient;

    public function __construct(
        BlizzardAuthClient $authClient,
        BlizzardUserClient $userClient
    )
    {
        $this->authClient = $authClient;
        $this->userClient = $userClient;
    }

    public function refreshAndCacheAccessToken()
    {
        $authResponse = $this->authClient->getToken();

        $token = $authResponse->access_token;

        cache(['token' => $token], now()->addSeconds($authResponse->expires_in - 1000));

        return $token;
    }

    public function syncBattleNetDetails(string $region, string $code, string $redirectUri)
    {
        $authResponse = $this->authClient->getOauthAccessToken($region, $code, $redirectUri);

        $token = $authResponse->access_token;

        $responses = $this->userClient->getUserInfoAndCharacters($token, $region);

        $this->saveOauthDetails($responses['oauth'], $region);
        $this->retrieveCharactersForAccount($responses['characters'], $region);
    }

    private function retrieveCharactersForAccount(Response $response, $region)
    {
        $data = json_decode($response->getBody());
        $characters = $data->wow_accounts[0]->characters;

        $savedCharacters = [];
        $ownerId = auth()->user()->id;

        foreach ($characters as $character) {
            try {
                $singleCharacter = app(CharacterService::class)->getBasicCharacterInfo(
                    $region, $character->realm->slug, $character->name, $ownerId
                );

                array_push($savedCharacters, $singleCharacter);
            } catch (ApiException $e) {
                Log::error('Exception while retrieving characters for user', ['exception_thrown' => $e->getMessage(), 'user' => auth()->user()->toArray()]);
                continue;
            }
        }

        return $savedCharacters;
    }

    private function saveOauthDetails(Response $response, $region): void
    {
        $data = json_decode($response->getBody());

        $user = auth()->user();
        $user->bnet_sync_at = now();
        $user->bnet_id = $data->id;
        $user->bnet_tag = $data->battletag;
        $user->bnet_region = $region;
        $user->save();
    }

}
