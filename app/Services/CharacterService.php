<?php


namespace App\Services;


use App\Services\Blizzard\BlizzardProfileClient;
use Illuminate\Support\Str;

class CharacterService
{
    private BlizzardProfileClient $profileClient;

    public function __construct($locale)
    {
        $this->profileClient = app(BlizzardProfileClient::class, ['locale' => $locale]);
    }

    public function getBasicCharacterInfo(string $realmName, string $characterInfo): array
    {
        $realmName = Str::slug($realmName);

        return $this->getCharacter($realmName, $characterInfo) + $this->getCharacterMedia($realmName, $characterInfo);
    }

    private function getCharacter(string $realmName, string $characterName): array
    {
        $response = $this->profileClient->getCharacterBasicInfo($realmName, $characterName);
        $character = json_decode($response->getBody());

        return [
            'id' => $character->id,
            'name' => $character->name,
            'gender' => ucfirst(strtolower($character->gender->type)),
            'faction' => ucfirst(strtolower($character->faction->type)),
            'race' => $character->race->id,
            'class' => $character->character_class->id,
            'realm' => Str::deslug($character->realm->slug),
            'guild' => [
                'id' => $character->guild->id,
                'name' => $character->guild->name,
                'realm' => Str::deslug($character->guild->realm->slug),
            ],
            'level' => $character->level,
            'achievementPoints' => $character->achievement_points,
            'averageItemLevel' => $character->average_item_level,
            'equippedItemLevel' => $character->equipped_item_level
        ];
    }

    private function getCharacterMedia(string $realmName, string $characterName): array
    {
        $response = $this->profileClient->getCharacterMedia($realmName, $characterName);
        $media = json_decode($response->getBody());

        return [
            'media' => [
                'avatar' => $media->avatar_url,
                'bust' => $media->bust_url,
                'render' => $media->render_url
            ]
        ];
    }
}
