<?php

namespace App\Http\Controllers;

use App\Jobs\RetrieveGuildRoster;
use App\Services\GuildService;

class GuildController extends Controller
{
    private GuildService $guildService;

    public function __construct(GuildService $guildService)
    {
        $this->guildService = $guildService;
    }

    public function guild(string $region, string $realm, string $guildName)
    {
        $guild = $this->guildService->getGuild($region, $realm, $guildName);

        if(!isset($guild->roster_synced_at) ||
            $guild->roster_synced_at->diffInSeconds() > config('blizzard.guild_min_seconds_update')) {
            RetrieveGuildRoster::dispatch($guild);
        }

        return response()->json([
            'guild' => $guild
        ]);
    }

    public function popular()
    {
        return response()->json([
            'recently_searched' => $this->guildService->getRecentlySearched(),
            'most_popular' => $this->guildService->getMostPopular()
        ]);
    }

}
