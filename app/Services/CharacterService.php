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
        $data = json_decode($response->getBody());

        return [
            'id' => $data->id,
            'name' => $data->name,
            'gender' => ucfirst(strtolower($data->gender->type)),
            'faction' => ucfirst(strtolower($data->faction->type)),
            'race' => $data->race->id,
            'class' => $data->character_class->id,
            'realm' => Str::deslug($data->realm->slug),
            'guild' => [
                'id' => $data->guild->id,
                'name' => $data->guild->name,
                'realm' => Str::deslug($data->guild->realm->slug),
            ],
            'level' => $data->level,
            'achievementPoints' => $data->achievement_points,
            'averageItemLevel' => $data->average_item_level,
            'equippedItemLevel' => $data->equipped_item_level
        ];
    }

    private function getCharacterMedia(string $realmName, string $characterName): array
    {
        $response = $this->profileClient->getCharacterMedia($realmName, $characterName);
        $data = json_decode($response->getBody());

        return [
            'media' => [
                'avatar' => $data->avatar_url,
                'bust' => $data->bust_url,
                'render' => $data->render_url
            ]
        ];
    }
}
