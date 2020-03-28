<?php

namespace App\Http\Controllers;


use App\Rules\RegionRule;
use App\Services\Blizzard\BlizzardAuthClient;
use App\Services\Blizzard\BlizzardUserClient;
use App\Services\BlizzardAuthService;

class BlizzardController extends Controller
{
    private BlizzardAuthService $blizzardAuthService;

    public function __construct()
    {
        request()->validate([
            'locale' => ['required', new RegionRule]
        ]);

        $this->blizzardAuthService = app(BlizzardAuthService::class);
    }

    public function code()
    {
        request()->validate([
            'code' => 'required',
            'redirectUri' => 'required'
        ]);

        $data = $this->blizzardAuthService->retrieveBlizzardAccountDetails(
            request('code'),
            request('redirectUri'),
            request('locale')
        );


        return response(['message' => 'Success!', 'data' => $data], 200);
    }
}
