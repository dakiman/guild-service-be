<?php

namespace App\Services\Blizzard;

use App\DTO\Guild\BlizzardGuild;
use App\DTO\Guild\GuildAchievement;
use App\DTO\Guild\RosterCharacter;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;

class GuildService
{
    private BlizzardProfileClient $profileClient;
    private string $locale;

    public function __construct($locale, BlizzardProfileClient $profileClient)
    {
        $this->locale = $locale;
        $this->profileClient = $profileClient;
    }

    public function getFullGuildInfo(string $realmName, string $guildName, string $locale)
    {
        $realmName = Str::slug($realmName);
        $guildName = Str::slug($guildName);

        $responses = $this->profileClient->getGuildInfo($realmName, $guildName, $locale);

        $guild = $this->getGuildFromResponse($responses['basic']);
        $guild->roster = $this->getRosterFromResponse($responses['roster']);
        $guild->achievements = $this->getAchievementsFromResponse($responses['achievements']);

        return $guild;
    }

    private function getGuildFromResponse(Response $response): BlizzardGuild
    {
        $guild = json_decode($response->getBody());
        return BlizzardGuild::fromData($guild);
    }

    /** @return RosterCharacter[] */
    private function getRosterFromResponse(Response $response)
    {
        $data = json_decode($response->getBody());

        $roster = [];
        foreach ($data->members as $member) {
            array_push($roster, RosterCharacter::fromData($member, $this->locale));
        }

        return $roster;
    }

    /** @return GuildAchievement[] */
    private function getAchievementsFromResponse(Response $response)
    {
        $data = json_decode($response->getBody());

        $achievements = [];
        foreach ($data->achievements as $singleAchievement) {
            array_push($achievements, GuildAchievement::fromData($singleAchievement));
        }

        return $achievements;
    }

}
