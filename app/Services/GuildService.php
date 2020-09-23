<?php

namespace App\Services;

use App\Guild;
use App\Services\Blizzard\BlizzardProfileClient;
use Illuminate\Support\Str;

class GuildService
{
    private BlizzardProfileClient $profileClient;

    public function __construct(BlizzardProfileClient $profileClient)
    {
        $this->profileClient = $profileClient;
    }

    public function getFullGuildInfo(string $region, string $realmName, string $guildName)
    {
        $realmName = Str::slug($realmName);
        $guildName = Str::slug($guildName);

        $guild = Guild
            ::where('name', $guildName)
            ->where('realm', $realmName)
            ->where('region', $region)
            ->first();

        if ($guild) {
            $guild->increasePopularity();
            $guild->save();
        } else {
            $guild = Guild::create([
                'name' => $guildName,
                'realm' => $realmName,
                'region' => $region,
                'guild_data' => $this->getGuildData($region, $realmName, $guildName)
            ]);
        }

        return $guild;
    }

    public function getRecentlySearched()
    {
        return Guild
            ::orderBy('updated_at', 'desc')
            ->limit(5)
            ->get(['name', 'region', 'realm']);
    }

    public function getMostPopular()
    {
        return Guild
            ::orderBy('num_of_searches', 'desc')
            ->limit(5)
            ->get(['name', 'region', 'realm']);
    }

    private function getGuildData(string $region, string $realmName, string $guildName)
    {
        $responses = $this->profileClient->getGuildInfo($region, $realmName, $guildName);

        $guildData = json_decode($responses['basic']->getBody());
        $guildData->roster = json_decode($responses['roster']->getBody());
        $guildData->achievements = json_decode($responses['achievements']->getBody());
        return $guildData;
    }

}
