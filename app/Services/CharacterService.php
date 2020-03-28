<?php


namespace App\Services;


use App\Character;
use App\DTO\Character\BlizzardCharacter;
use App\DTO\Character\CharacterMedia;
use App\DTO\Character\EquipmentItem;
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

    public function getBasicCharacterInfo(string $region, string $realmName, string $characterName): BlizzardCharacter
    {
        $realmName = Str::slug($realmName);
        $characterName = strtolower($characterName);

        $character = Character::where([
            'name' => $characterName,
            'realm' => $realmName,
            'region' => $region
        ])->first();

        if ($character) {
            return new BlizzardCharacter(json_decode($character->character_data, true));
        } else {
            $responses = $this->profileClient->getCharacterInfo($region, $realmName, $characterName);

            $character = $this->getCharacterFromResponse($responses['basic']);
            $character->media = $this->getCharacterMediaFromResponse($responses['media']);
            $character->equipment = $this->getEquipmentFromResponse($responses['equipment']);

            Character::create([
                'name' => $characterName,
                'realm' => $realmName,
                'region' => $region,
                'character_data' => json_encode($character)
            ]);
        }

        return $character;
    }

    public function retrieveCharactersFromAccount($token, $region)
    {
        $accountData = $this->profileClient->getUserCharacters($token, $region);
        $characters = $accountData->wow_accounts[0]->characters;

        $savedCharacters = [];
        foreach ($characters as $character) {
            try {
                $singleCharacter = $this->getBasicCharacterInfo(
                    $region, $character->realm->slug, $character->name
                );

                array_push($savedCharacters, $singleCharacter);
            } catch (BlizzardServiceException $e) {
                continue;
            }

        }

        return $savedCharacters;
    }

    private function getCharacterFromResponse($response): BlizzardCharacter
    {
        $character = json_decode($response->getBody());
        return BlizzardCharacter::fromData($character);
    }

    private function getCharacterMediaFromResponse($response): CharacterMedia
    {
        $media = json_decode($response->getBody());
        return CharacterMedia::fromData($media);
    }

    /** @return EquipmentItem[] */
    private function getEquipmentFromResponse($response)
    {
        $data = json_decode($response->getBody());

        $items = [];
        foreach ($data->equipped_items as $item) {
            array_push($items, EquipmentItem::fromData($item));
        }

        return $items;
    }
}
