<?php


namespace App\Services;

use App\Models\Character;
use App\Exceptions\BlizzardServiceException;
use App\Services\Blizzard\BlizzardProfileClient;
use Str;

class CharacterService
{
    private BlizzardProfileClient $profileClient;

    public function __construct(BlizzardProfileClient $profileClient)
    {
        $this->profileClient = $profileClient;
    }

    public function getBasicCharacterInfo(string $region, string $realmName, string $characterName, string $ownerId = null): Character
    {
        $realmName = Str::slug($realmName);
        $characterName = strtolower($characterName);

        $character = Character::where([
            'name' => $characterName,
            'realm' => $realmName,
            'region' => $region
        ])->first();

        if ($character) {
            if ($ownerId != null) {
                $character->user_id = $ownerId;
            }
            $character->increasePopularity();
            $character->save();
        } else {
            $character = Character::create([
                'name' => $characterName,
                'realm' => $realmName,
                'region' => $region,
                'user_id' => $ownerId,
                'character_data' => $this->getCharacterData($region, $realmName, $characterName)
            ]);
        }
        return $character;
    }

    public function retrieveCharactersFromAccount($token, $region)
    {
        $accountData = $this->profileClient->getUserCharacters($token, $region);
        $characters = $accountData->wow_accounts[0]->characters;

        $savedCharacters = [];
        $ownerId = auth()->user()->id;

        foreach ($characters as $character) {
            try {
                $singleCharacter = $this->getBasicCharacterInfo(
                    $region, $character->realm->slug, $character->name, $ownerId
                );

                array_push($savedCharacters, $singleCharacter);
            } catch (BlizzardServiceException $e) {
                continue;
            }

        }

        return $savedCharacters;
    }

    public function getRecentlySearched()
    {
        return Character
            ::orderBy('updated_at', 'desc')
            ->limit(5)
            ->get(['name', 'region', 'realm']);
    }

    public function getMostPopular()
    {
        return Character
            ::orderBy('num_of_searches', 'desc')
            ->limit(5)
            ->get(['name', 'region', 'realm']);
    }

    private function getCharacterData(string $region, string $realmName, string $characterName)
    {
        $responses = $this->profileClient->getCharacterInfo($region, $realmName, $characterName);
        $characterData = json_decode($responses['basic']->getBody());
        $characterData->media = json_decode($responses['media']->getBody());
        $characterData->equipment = json_decode($responses['equipment']->getBody());
        return $characterData;
    }

}
