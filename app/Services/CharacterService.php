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

        $character = $this->getCharacter($responses['basic']);
        $character->media = $this->getCharacterMedia($responses['media']);
        $character->equipment = $this->getEquipment($responses['equipment']);
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

    private function getCharacter($response): BlizzardCharacter
    {
        $character = json_decode($response->getBody());
        return BlizzardCharacter::fromData($character);
    }

    private function getCharacterMedia($response): CharacterMedia
    {
        $media = json_decode($response->getBody());
        return CharacterMedia::fromData($media);
    }

    private function getEquipment($response)
    {
        $data = json_decode($response->getBody());
        return EquipmentItem::fromData($data);
//        return collect($data->equipped_items)
//            ->map(function ($item) {
//
//                $parsedItem = [
//                    'id' => $item->item->id,
//                    'name' => $item->name,
//                    'quality' => $item->quality->name,
//                    'itemLevel' => $item->level->value,
//                ];
//
//                /*TODO Reconsider implementation */
//                if (!empty($item->sockets)) {
//                    $parsedItem['sockets'] = [];
//                    foreach ($item->sockets as $socket)
//                        array_push(
//                            $parsedItem['sockets'],
//                            ['gem' => $socket->item->id ?? null, 'type' => $socket->socket_type->name]
//                        );
//                }
//
//                return $parsedItem;
//            });
    }
}
