<?php

namespace App\Services;

use App\Guild;
use App\Services\Blizzard\BlizzardProfileClient;
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
            return $guild;
        } else {
            $responses = $this->profileClient->getGuildInfo($region, $realmName, $guildName);

            $guildData = json_decode($responses['basic']->getBody());
            $guildData->roster = json_decode($responses['roster']->getBody());
            $guildData->achievements = json_decode($responses['achievements']->getBody());

            $guild = Guild::create([
                'name' => $guildName,
                'realm' => $realmName,
                'region' => $region,
                'guild_data' => json_encode($guildData)
            ]);
        }

        return $guild;
    }

}
