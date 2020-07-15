<?php

namespace App\Http\Controllers;

use App\Character;
use App\Services\CharacterService;

class CharacterController extends Controller
{
    private CharacterService $characterService;

    public function __construct(CharacterService $characterService)
    {
        $this->characterService = app()->make(CharacterService::class);
    }

    public function character(string $region, string $realm, string $characterName)
    {
        $character = $this->characterService->getBasicCharacterInfo($region, $realm, $characterName);
        return response()->json(['character' => $character], 200);
    }

    public function toggleRecruitment(Character $character)
    {
        $character->recruitment = !$character->recruitment;
        $character->save();

        return response()->json(['message' => 'Recruitment status toggled!', 'status' => $character->recruitment]);
    }

}
