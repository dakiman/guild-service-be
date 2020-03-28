<?php


namespace App\Services;


use App\Exceptions\BlizzardServiceException;
use App\Services\Blizzard\BlizzardAuthClient;
use App\Services\Blizzard\BlizzardUserClient;

class BlizzardAuthService
{
    private BlizzardAuthClient $blizzardAuthClient;
    private BlizzardUserClient $blizzardUserClient;
    private CharacterService $characterService;

    public function __construct(CharacterService $characterService)
    {
        $this->characterService = $characterService;
    }

    public function retrieveBlizzardAccountDetails(string $authCode, string $redirectUri, string $region)
    {
        // call auth client to get oauth token
        $this->initClient($region);
        $token = $this->blizzardAuthClient->retrieveOauthAccessToken($authCode, $redirectUri);
        // call ? client with token to get user data and characters
        $responses = $this->blizzardUserClient->getUserInfoAndCharacters($token, $region);
        // save user data
        $this->saveBlizzardDetailsFromResponse($responses['oauth']);
        // save characters
        return $this->saveUserCharacters($responses['characters'], $region);
//        return json_decode($responses['characters']->getBody());
    }

    private function initClient($region)
    {
        $this->blizzardAuthClient = app(BlizzardAuthClient::class, ['region' => $region]);
        $this->blizzardUserClient = app(BlizzardUserClient::class, ['region' => $region]);
    }

    private function saveBlizzardDetailsFromResponse($blizzardOauthData)
    {
        $blizzardOauthData = json_decode($blizzardOauthData->getBody());

        $user = auth()->user();
        $user->blizzard_id = $blizzardOauthData->id;
        $user->battle_tag = $blizzardOauthData->battletag;
        $user->save();
    }

    private function saveUserCharacters($charactersResponse, $region)
    {
        $accountData = json_decode($charactersResponse->getBody());
        $characters = $accountData->wow_accounts[0]->characters;

        $savedCharacters = [];
        foreach ($characters as $character) {
            try {
                $singleCharacter = $this->characterService->getBasicCharacterInfo(
                    $character->realm->slug,
                    $character->name,
                    $region
                );

                array_push($savedCharacters, $singleCharacter);
            } catch (BlizzardServiceException $e) {
                continue;
            }

        }

        return $savedCharacters;
    }


}
