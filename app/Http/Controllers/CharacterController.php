<?php

namespace App\Http\Controllers;

use App\Services\CharacterService;

class CharacterController extends Controller
{
    private CharacterService $characterService;

    public function __construct()
    {
        $this->characterService = app()->make(CharacterService::class);
    }

    public function character(string $region, string $realm, string $characterName)
    {
        $character = $this->characterService->getBasicCharacterInfo($realm, $characterName, $region);
        return response()->json(['character' => $character], 200);
    }

}
