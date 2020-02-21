<?php


namespace App\Services;


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

        return [
            'id' => $character->id,
            'name' => $character->name,
            'gender' => ucfirst(strtolower($character->gender->type)),
            'faction' => ucfirst(strtolower($character->faction->type)),
            'race' => $character->race->id,
            'class' => $character->character_class->id,
            'realm' => Str::deslug($character->realm->slug),
            'guild' => isset($character->guild) ? [
                'id' => $character->guild->id,
                'name' => $character->guild->name,
                'realm' => Str::deslug($character->guild->realm->slug),
            ] : null,
            'level' => $character->level,
            'achievementPoints' => $character->achievement_points,
            'averageItemLevel' => $character->average_item_level,
            'equippedItemLevel' => $character->equipped_item_level
        ];
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
}
