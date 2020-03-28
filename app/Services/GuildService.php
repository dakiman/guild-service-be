<?php

namespace App\Services\Blizzard;

use App\DTO\Guild\BlizzardGuild;
use App\DTO\Guild\GuildAchievement;
use App\DTO\Guild\RosterCharacter;
use App\Guild;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;

class GuildService
{
    private BlizzardProfileClient $profileClient;
    private string $region;

    public function __construct($region, BlizzardProfileClient $profileClient)
    {
        $this->region = $region;
        $this->profileClient = $profileClient;
    }

    public function getFullGuildInfo(string $region, string $realmName, string $guildName): BlizzardGuild
    {
        $realmName = Str::slug($realmName);
        $guildName = Str::slug($guildName);

        $guild = Guild
            ::where('name', $guildName)
            ->where('realm', $realmName)
            ->where('region', $region)
            ->first();

        if ($guild) {
            return new BlizzardGuild(json_decode($guild->guild_data, true));
        } else {
            $responses = $this->profileClient->getGuildInfo($region, $realmName, $guildName);

            $guild = $this->getGuildFromResponse($responses['basic']);
            $guild->roster = $this->getRosterFromResponse($responses['roster']);
            $guild->achievements = $this->getAchievementsFromResponse($responses['achievements']);

            Guild::create([
                'name' => $guildName,
                'realm' => $realmName,
                'region' => $region,
                'guild_data' => json_encode($guild)
            ]);
        }

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
            array_push($roster, RosterCharacter::fromData($member, $this->region));
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
