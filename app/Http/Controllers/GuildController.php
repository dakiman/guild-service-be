<?php

namespace App\Http\Controllers;

use App\Services\Blizzard\GuildService;
use Illuminate\Validation\Rule;

class GuildController extends Controller
{
    private GuildService $guildService;

    public function __construct()
    {
        request()->validate([
            'locale' => [
                'required',
                Rule::in(array_merge(array_map("strtolower", config('blizzard.regions')), config('blizzard.regions')))
            ]
        ]);

        $this->guildService = app()->make(GuildService::class, ['locale' => request('locale')]);
    }

    public function guildFull($realm, $guild)
    {
        $guild = $this->guildService->getFullGuildInfo($realm, $guild);
        return response()->json(['guild' => $guild], 200);
    }

    public function guildBasic($realm, $guild)
    {
        $guild = $this->guildService->getBasicGuildInfo($realm, $guild);
        return response()->json(['guild' => $guild], 200);
    }

}
