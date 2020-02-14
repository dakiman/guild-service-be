<?php

namespace App\Http\Controllers;

use App\Services\CharacterService;
use Illuminate\Validation\Rule;

class CharacterController extends Controller
{
    private CharacterService $characterService;

    public function __construct()
    {
        request()->validate([
            'locale' => [
                'required',
                Rule::in(array_merge(array_map("strtolower", config('blizzard.regions')), config('blizzard.regions')))
            ]
        ]);

        $this->characterService = app()->make(CharacterService::class, ['locale' => request('locale')]);
    }

    public function character(string $realm, string $characterName)
    {
        $character = $this->characterService->getBasicCharacterInfo($realm, $characterName);
        return response()->json(['character' => $character], 200);
    }

}
