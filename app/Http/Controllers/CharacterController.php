<?php

namespace App\Http\Controllers;

use App\Rules\RegionRule;
use App\Services\CharacterService;

class CharacterController extends Controller
{
    private CharacterService $characterService;

    public function __construct()
    {
        request()->validate([
            'locale' => ['required', new RegionRule]
        ]);

        $this->characterService = app()->make(CharacterService::class, ['locale' => request('locale')]);
    }

    public function character(string $realm, string $characterName)
    {
        $character = $this->characterService->getBasicCharacterInfo($realm, strtolower($characterName));
        return response()->json(['character' => $character], 200);
    }

}
