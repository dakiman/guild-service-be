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

    public function code($region)
    {
        request()->validate([
            'code' => 'required',
            'redirectUri' => 'required'
        ]);

        $token = $this->blizzardAuthService->syncBattleNetDetailsAndGetToken(
            request('region'),
            request('code'),
            request('redirectUri')
        );

        $characters = $this->characterService->retrieveCharactersFromAccount($token, $region);

        return response(['message' => 'Success!', 'characters' => $characters], 200);
    }
}
