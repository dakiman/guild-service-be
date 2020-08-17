<?php

namespace App\Http\Controllers;

use App\Character;
use App\Services\CharacterService;

class CharacterController extends Controller
{
    private CharacterService $characterService;

    public function __construct(CharacterService $characterService)
    {
        $this->characterService = $characterService;
    }

    public function character(string $region, string $realm, string $characterName)
    {
        return response()->json([
            'character' => $this->characterService->getBasicCharacterInfo($region, $realm, $characterName)
        ]);
    }

    public function toggleRecruitment(Character $character)
    {
        $character->recruitment = !$character->recruitment;
        $character->save();

        return response()->json(['message' => 'Recruitment status toggled!', 'status' => $character->recruitment]);
    }

    public function popular()
    {
        return response()->json([
            'recently_searched' => $this->characterService->getRecentlySearched(),
            'most_popular' => $this->characterService->getMostPopular()
        ]);
    }

}
