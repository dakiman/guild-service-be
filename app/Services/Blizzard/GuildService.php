<?php

namespace App\Services;

use App\Services\Blizzard\BlizzardDataClient;
use Illuminate\Support\Str;

class GuildService
{
    private $dataClient;

    public function __construct(BlizzardDataClient $dataClient)
    {
        $this->dataClient = $dataClient;
    }

    public function getGuild(string $realmName, string $guildName)
    {
        $realmName = Str::slug($realmName);
        $guildName = Str::slug($guildName);

        return [
            'guild' => [
                'roster' => $this->getRoster($realmName, $guildName),
                'achievements' => $this->getAchievements($realmName, $guildName)
            ]
        ];
    }

    private function getRoster(string $realmName, string $guildName)
    {
        $response = $this->dataClient->getGuildRoster($realmName, $guildName);
        $roster = json_decode($response->getBody());

        $guildMembers = collect($roster->members)
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

        return $guildMembers;
    }

    private function getAchievements(string $realmName, string $guildName)
    {
        $response = $this->dataClient->getGuildAchievements($realmName, $guildName);
        $achievements = json_decode($response->getBody());

        $guildAchievements = collect($achievements->achievements)
            ->map(function ($achievement) {
                return [
                    'id' => $achievement->id,
                    'name' => $achievement->achievement->name,
                    'criteria' => [
                        'id' => $achievement->criteria->id,
                        'completed' => $achievement->criteria->is_completed
                    ],
                    'completedAt' => $achievement->completed_timestamp ?? null
                ];
            });

        return $guildAchievements;
    }

}
