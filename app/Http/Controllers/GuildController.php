<?php

namespace App\Http\Controllers;

use App\Services\Blizzard\GuildService;

class GuildController extends Controller
{
    private GuildService $guildService;

    public function __construct()
    {
        $this->guildService = app(GuildService::class, ['region' => request('region')]);
    }

    public function guild(string $region, string $realm, string $guild)
    {
        $guild = $this->guildService->getFullGuildInfo($region, $realm, $guild);
        return response()->json(['guild' => $guild], 200);
    }

}
