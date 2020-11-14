<?php

namespace App\Http\Controllers;


use App\Services\BlizzardAuthService;

class BlizzardController extends Controller
{
    private BlizzardAuthService $blizzardAuthService;

    public function __construct(BlizzardAuthService $blizzardAuthService)
    {
        $this->blizzardAuthService = $blizzardAuthService;
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
