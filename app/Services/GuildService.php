<?php

namespace App\Services;

use App\DTO\Guild\GuildBasic;
use App\DTO\Guild\GuildDocument;
use App\DTO\Guild\GuildMember;
use App\Jobs\RetrieveGuildRoster;
use App\Models\Guild;
use App\Services\Blizzard\BlizzardProfileClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;

class GuildService
{
    private BlizzardProfileClient $profileClient;

    public function __construct(BlizzardProfileClient $profileClient)
    {
        $this->profileClient = $profileClient;
    }

    public function getGuild(string $region, string $realmName, string $guildName)
    {
        $realmName = Str::slug($realmName);
        $guildName = Str::slug($guildName);

        $guild = Guild::where([
            'name' => $guildName,
            'realm' => $realmName,
            'region' => $region,
        ])->first();

        if (
            !$guild ||
            $guild->updated_at->diffInSeconds() > config('blizzard.guild_min_seconds_update')
        ) {
            $responses = $this->profileClient->getGuildInfo($region, $realmName, $guildName);

            $guildDocument = new GuildDocument([
                'name' => $guildName,
                'realm' => $realmName,
                'region' => $region,
                'num_of_searches' => optional($guild)->num_of_searches ? ++$guild->num_of_searches : 1,
                'basic' => $this->mapBasicData($responses['basic']),
                'roster' => $this->mapRosterData($responses['roster']),
            ]);

            $guild = Guild::updateOrCreate([
                'name' => $guildName,
                'realm' => $realmName,
                'region' => $region,
            ],
                $guildDocument->toArray()
            );

            RetrieveGuildRoster::dispatch($guild);
        }

        return $guild;
    }

    private function mapBasicData(Response $response): GuildBasic
    {
        $basicData = json_decode($response->getBody());

        return new GuildBasic([
            'achievement_points' => $basicData->achievement_points,
            'member_count' => $basicData->member_count,
            'created_timestamp' => $basicData->created_timestamp,
            'faction' => $basicData->faction->name
        ]);
    }

    /** @return GuildMember[] */
    private function mapRosterData(Response $response)
    {
        $roster = json_decode($response->getBody());

        return collect($roster->members)->map(function ($member) {
            $character = $member->character;

            $member = new GuildMember([
                'name' => $character->name,
                'realm' => $character->realm->slug,
                'level' => $character->level,
                'class' => $character->playable_class->id,
                'race' => $character->playable_race->id,
                'rank' => $member->rank,
            ]);

            return $member;
        })->toArray();
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

}
