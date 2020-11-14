<?php

namespace App\Http\Controllers;

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
        return response()->json([
            'guild' => $this->guildService->getGuild($region, $realm, $guildName)
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
