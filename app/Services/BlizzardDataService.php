<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

class BlizzardDataService
{
    private $client;

    public function __construct(BlizzardAuthenticationService $blizzardAuthenticationService)
    {
        $this->client = new Client([
            'headers' => ['Authorization' => 'Bearer ' . $blizzardAuthenticationService->retrieveToken()],
            'base_uri' => 'https://eu.api.blizzard.com/data/wow/'
        ]);
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
        $responseRoster = $this->client->get("guild/$realmName/$guildName/roster?namespace=profile-eu&locale=en_EU");
        $roster = json_decode($responseRoster->getBody());

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
        $responseAchievements = $this->client->get("guild/$realmName/$guildName/achievements?namespace=profile-eu&locale=en_EU");
        $achievements = json_decode($responseAchievements->getBody());

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
