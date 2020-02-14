<?php

namespace App\Services\Blizzard;

use Illuminate\Support\Str;

class GuildService
{
    private BlizzardProfileClient $profileClient;

    public function __construct($locale)
    {
        $this->profileClient = app(BlizzardProfileClient::class, ['locale' => $locale]);
    }

    public function getFullGuildInfo(string $realmName, string $guildName)
    {
        $realmName = Str::slug($realmName);
        $guildName = Str::slug($guildName);

        $data = $this->getGuild($realmName, $guildName);
        $data['roster'] = $this->getRoster($realmName, $guildName);
        $data['achievements'] = $this->getAchievements($realmName, $guildName);

        return $data;
    }

    public function getBasicGuildInfo(string $realmName, string $guildName): array
    {
        $realmName = Str::slug($realmName);
        $guildName = Str::slug($guildName);

        return $this->getGuild($realmName, $guildName);
    }

    private function getGuild(string $realmName, string $guildName): array
    {
        $response = $this->profileClient->getGuildBasicInfo($realmName, $guildName);
        $guild = json_decode($response->getBody());

        return [
            'id' => $guild->id,
            'name' => $guild->name,
            'faction' => ucfirst(strtolower($guild->faction->type)),
            'achievementPoints' => $guild->achievement_points,
            'memberCount' => $guild->member_count,
            'realm' => Str::deslug($guild->realm->slug),
            'created' => $guild->created_timestamp
        ];
    }

    private function getRoster(string $realmName, string $guildName)
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
            });
    }

    private function getAchievements(string $realmName, string $guildName)
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
            });
    }

}
