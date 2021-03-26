<?php

namespace App\Http\Controllers;


use App\Jobs\SyncBnetData;
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

        SyncBnetData::dispatch(
            request('region'),
            request('code'),
            request('redirectUri'),
            auth()->user()
        );

        return response(['message' => 'Sync job dispatched!'], 202);
    }
}
