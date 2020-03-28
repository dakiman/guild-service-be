<?php


namespace App\Services;


use App\Character;
use App\DTO\Character\BlizzardCharacter;
use App\DTO\Character\CharacterMedia;
use App\DTO\Character\EquipmentItem;
use App\Services\Blizzard\BlizzardProfileClient;
use App\Services\Raiderio\RaiderioClient;
use Str;

class CharacterService
{
    private BlizzardProfileClient $profileClient;
    private RaiderioClient $raiderioClient;

    public function __construct(BlizzardProfileClient $profileClient)
    {
        $this->profileClient = $profileClient;
    }

    public function getBasicCharacterInfo(string $realmName, string $characterName, string $locale): BlizzardCharacter
    {
        $realmName = Str::slug($realmName);
        $characterName = strtolower($characterName);

        $character = Character::where([
            'name' => $characterName,
            'realm' => $realmName,
            'region' => $locale
        ])->first();

        if ($character) {
            return new BlizzardCharacter(json_decode($character->character_data, true));
        } else {
            $responses = $this->profileClient->getCharacterInfo($realmName, $characterName, $locale);

            $character = $this->getCharacterFromResponse($responses['basic']);
            $character->media = $this->getCharacterMediaFromResponse($responses['media']);
            $character->equipment = $this->getEquipmentFromResponse($responses['equipment']);

            Character::create([
                'name' => $characterName,
                'realm' => $realmName,
                'region' => $locale,
                'character_data' => json_encode($character)
            ]);
        }

        return $character;
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
