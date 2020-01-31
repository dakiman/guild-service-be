<?php

namespace App\Services\Blizzard;

use Illuminate\Support\Str;

class GuildService
{
    private BlizzardProfileClient $profileClient;

    public function __construct(BlizzardProfileClient $profileClient)
    {
        $this->profileClient = $profileClient;
    }

    public function getFullGuildInfo(string $realmName, string $guildName)
    {
        $realmName = Str::slug($realmName);
        $guildName = Str::slug($guildName);

        $data = [
            'guild' => $this->getGuild($realmName, $guildName)
        ];

        $data['guild']['roster'] = $this->getRoster($realmName, $guildName);
        $data['guild']['achievements'] = $this->getAchievements($realmName, $guildName);

        return $data;
    }

    public function getBasicGuildInfo(string $realmName, string $guildName): array
    {
        $realmName = Str::slug($realmName);
        $guildName = Str::slug($guildName);

        return [
            'guild' => $this->getGuild($realmName, $guildName)
        ];
    }

    private function getGuild(string $realmName, string $guildName): array
    {
        $response = $this->profileClient->getGuildBasicInfo($realmName, $guildName);
        $data = json_decode($response->getBody());

        return [
            'id' => $data->id,
            'name' => $data->name,
            'faction' => ucfirst(Str::lower($data->faction->type)),
            'achievementPoints' => $data->achievement_points,
            'memberCount' => $data->member_count,
            'realm' => Str::deslug($data->realm->slug),
            'created' => $data->created_timestamp
        ];
    }

    private function getRoster(string $realmName, string $guildName): array
    {
        $response = $this->profileClient->getGuildRoster($realmName, $guildName);
        $data = json_decode($response->getBody());

        return collect($data->members)
            ->map(function ($member) {
                $character = $member->character;
                return [
                    'name' => $character->name,
                    'level' => $character->level,
                    'realm' => Str::deslug($character->realm->slug),
                    'class' => $character->playable_class->id,
                    'race' => $character->playable_race->id,
                    'rank' => $member->rank
                ];
            })->toArray();
    }

    private function getAchievements(string $realmName, string $guildName): array
    {
        $response = $this->profileClient->getGuildAchievements($realmName, $guildName);
        $data = json_decode($response->getBody());

        return collect($data->achievements)
            ->map(function ($achievement) {
                return [
                    'id' => $achievement->id,
                    'name' => $achievement->achievement->name,
//                    'criteria' => [
//                        'id' => $achievement->criteria->id,
//                        'completed' => $achievement->criteria->is_completed
//                    ],
                    'completedAt' => $achievement->completed_timestamp ?? null
                ];
            })->toArray();
    }

}
