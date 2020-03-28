<?php

namespace App\Http\Controllers;


use App\Services\BlizzardAuthService;

class BlizzardController extends Controller
{
    private BlizzardAuthService $blizzardAuthService;

    public function __construct()
    {
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
            request('region')
        );


        return response(['message' => 'Success!', 'data' => $data], 200);
    }
}
