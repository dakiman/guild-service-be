<?php


namespace App\Services;


use App\DTO\BlizzardCharacter;
use App\Services\Blizzard\BlizzardProfileClient;
use App\Services\Raiderio\RaiderioClient;
use Illuminate\Support\Str;

class CharacterService
{
    private BlizzardProfileClient $profileClient;
    private RaiderioClient $raiderioClient;

    public function __construct($locale)
    {
        $this->profileClient = app(BlizzardProfileClient::class, ['locale' => $locale]);
        $this->raiderioClient = app(RaiderioClient::class);
    }

    public function getBasicCharacterInfo(string $realmName, string $characterName): array
    {
        $realmName = Str::slug($realmName);

        $responses = $this->profileClient->getCharacterInfo($realmName, $characterName);

        $data = $this->getCharacter($responses['basic']);
        $data['media'] = $this->getCharacterMedia($responses['media']);
        $data['equipment'] = $this->getEquipment($responses['equipment']);

        return $data;
    }

    public function getCharacterRaiderioData(string $realmName, string $characterName, string $locale)
    {
        $realmName = Str::slug($realmName);

        $response = $this->raiderioClient->getRaiderioInfo($realmName, $characterName, $locale);

        return $this->getRaiderioData($response);
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

    private function getCharacter($response): array
    {
        $character = json_decode($response->getBody());
        return BlizzardCharacter::fromData($character)->toArray();
    }

    private function getCharacterMedia($response): array
    {
        $media = json_decode($response->getBody());

        return [
            'avatar' => $media->avatar_url,
            'bust' => $media->bust_url,
            'render' => $media->render_url
        ];
    }

    private function getEquipment($response)
    {
        $data = json_decode($response->getBody());

        return collect($data->equipped_items)
            ->map(function ($item) {

                $parsedItem = [
                    'id' => $item->item->id,
                    'name' => $item->name,
                    'quality' => $item->quality->name,
                    'itemLevel' => $item->level->value,
                ];

                /*TODO Reconsider implementation */
                if (!empty($item->sockets)) {
                    $parsedItem['sockets'] = [];
                    foreach ($item->sockets as $socket)
                        array_push(
                            $parsedItem['sockets'],
                            ['gem' => $socket->item->id ?? null, 'type' => $socket->socket_type->name]
                        );
                }

                return $parsedItem;
            });
    }
}
