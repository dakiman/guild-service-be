<?php

namespace App\Http\Controllers;

use App\Exceptions\BlizzardServiceException;
use App\Services\GuildService;

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

    public function guild($realm, $guild)
    {
        $guild = $this->guildService->getGuild($realm, $guild);
        return response()->json($guild, 200);
    }

}
