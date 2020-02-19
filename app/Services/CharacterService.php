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

    public function getBasicCharacterInfo(string $realmName, string $characterName): array
    {
        $realmName = Str::slug($realmName);

        $responses = $this->profileClient->getCharacterInfo($realmName, $characterName);

        $data = $this->getCharacter($responses['basic']);
        $data['media'] = $this->getCharacterMedia($responses['media']);

        return $data;
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
