<?php

namespace App\Http\Controllers;

use App\Services\GuildService;

class GuildController extends Controller
{
    private $guildService;

    public function __construct(GuildService $guildService)
    {
        $this->guildService = $guildService;
    }

    public function guild($realm, $guild)
    {
        $guild = $this->guildService->getGuild($realm, $guild);
        return response()->json(['guild' => $guild], 200);
    }

}
