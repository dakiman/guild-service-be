<?php

namespace App\Http\Controllers;

use App\Rules\RegionRule;
use App\Services\Blizzard\GuildService;

class GuildController extends Controller
{
    private GuildService $guildService;

    public function __construct()
    {
        request()->validate([
            'locale' => ['required', new RegionRule]
        ]);

        $this->guildService = app(GuildService::class, ['locale' => request('locale')]);
    }

    public function guild($realm, $guild)
    {
        $guild = $this->guildService->getFullGuildInfo($realm, $guild, request('locale'));
        return response()->json(['guild' => $guild], 200);
    }

}
