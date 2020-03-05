<?php


namespace App\Services;


use App\DTO\BlizzardCharacter;
use App\DTO\CharacterMedia;
use App\DTO\EquipmentItem;
use App\Services\Blizzard\BlizzardProfileClient;
use App\Services\Raiderio\RaiderioClient;
use Illuminate\Support\Str;

class CharacterService
{
    private BlizzardProfileClient $profileClient;
    private RaiderioClient $raiderioClient;

    public function __construct(BlizzardProfileClient $profileClient, RaiderioClient $raiderioClient)
    {
        $this->profileClient = $profileClient;
        $this->raiderioClient = $raiderioClient;
    }

    public function getBasicCharacterInfo(string $realmName, string $characterName, string $locale): BlizzardCharacter
    {
        $realmName = Str::slug($realmName);

        $responses = $this->profileClient->getCharacterInfo($realmName, $characterName, $locale);

        $character = $this->getCharacterFromResponse($responses['basic']);
        $character->media = $this->getCharacterMediaFromResponse($responses['media']);
        $character->equipment = $this->getEquipmentFromResponse($responses['equipment']);
//        $data['raidingData'] = $this->getRaiderioData($this->raiderioClient->getRaiderioInfo($realmName, $characterName, $locale));

        return $character;
    }

    public function getRaiderioData($response): array
    {
        $raiderioData = json_decode($response->getBody());

        return [
            'mythicPlusRanks' => $raiderioData->mythic_plus_ranks,
            'gear' => $raiderioData->gear,
            'raidProgression' => $raiderioData->raid_progression
        ];
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
