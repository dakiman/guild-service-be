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
            return $character;
        } else {
            $responses = $this->profileClient->getCharacterInfo($region, $realmName, $characterName);

            $characterData = $this->getCharacterFromResponse($responses['basic']);
            $characterData->media = $this->getCharacterMediaFromResponse($responses['media']);
            $characterData->equipment = $this->getEquipmentFromResponse($responses['equipment']);

            $character = Character::create([
                'name' => $characterName,
                'realm' => $realmName,
                'region' => $region,
                'user_id' => $ownerId,
                'faction' => $characterData->faction,
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
