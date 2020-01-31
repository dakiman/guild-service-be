<?php

namespace App\Http\Controllers;

use App\Exceptions\BlizzardServiceException;
use App\Services\Blizzard\GuildService;

class GuildController extends Controller
{
    private GuildService $guildService;

    public function __construct(GuildService $guildService)
    {
        if(empty(request('locale'))) {
            throw new BlizzardServiceException('Cant call blizzard services without providing locale ', 400);
        }

        $this->guildService = $guildService;
    }

    public function guildFull($realm, $guild)
    {
        $guild = $this->guildService->getFullGuildInfo($realm, $guild);
        return response()->json($guild, 200);
    }

    public function guildBasic($realm, $guild)
    {
        $guild = $this->guildService->getBasicGuildInfo($realm, $guild);
        return response()->json($guild, 200);
    }

}
