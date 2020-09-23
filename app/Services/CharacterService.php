<?php


namespace App\Services;

use App\Character;
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

    public function getBasicCharacterInfo(string $region, string $realmName, string $characterName, int $ownerId = null): Character
    {
        $realmName = Str::slug($realmName);
        $characterName = strtolower($characterName);

        $character = Character::where([
            'name' => $characterName,
            'realm' => $realmName,
            'region' => $region
        ])->first();

        if ($character) {
            if($ownerId != null) {
                $character->user_id = $ownerId;
                $character->save();
            }
            return $character;
        } else {
            $responses = $this->profileClient->getCharacterInfo($region, $realmName, $characterName);

            $characterData = json_decode($responses['basic']->getBody());
            $characterData->media = json_decode($responses['media']->getBody());
            $characterData->equipment = json_decode($responses['equipment']->getBody());

            $character = Character::create([
                'name' => $characterName,
                'realm' => $realmName,
                'region' => $region,
                'user_id' => $ownerId,
                'character_data' => json_encode($characterData)
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

}
