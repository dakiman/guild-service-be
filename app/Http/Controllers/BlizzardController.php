<?php

namespace App\Http\Controllers;

use App\Services\BlizzardDataService;

class BlizzardController extends Controller
{
    private $blizzardService;

    public function __construct(BlizzardDataService $blizzardService)
    {
        $this->blizzardService = $blizzardService;
    }

    public function guild($realm, $guild)
    {
        $guild = $this->blizzardService->getGuild($realm, $guild);
        return response()->json(['guild' => $guild], 200);
    }

}
