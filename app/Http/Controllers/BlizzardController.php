<?php

namespace App\Http\Controllers;


use App\Services\BlizzardAuthService;
use App\Services\CharacterService;

class BlizzardController extends Controller
{
    private BlizzardAuthService $blizzardAuthService;
    private CharacterService $characterService;

    public function __construct(BlizzardAuthService $blizzardAuthService, CharacterService $characterService)
    {
        $this->blizzardAuthService = $blizzardAuthService;
        $this->characterService = $characterService;
    }

    public function code()
    {
        request()->validate([
            'code' => 'required',
            'redirectUri' => 'required'
        ]);

        $this->blizzardAuthService->syncBattleNetDetails(
            request('region'),
            request('code'),
            request('redirectUri')
        );

        return response(['message' => 'Successfully synced Battle.net data!'], 200);
    }
}
