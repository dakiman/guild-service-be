<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Exceptions\BlizzardServiceException;
use Tests\TestCase;

class CharacterTest extends TestCase
{
    private $characters = [
        ['name' => 'Sernaos', 'realm' => 'the-maelstrom', 'region' => 'eu'],
        ['name' => 'Vilwarr', 'realm' => 'illidan', 'region' => 'us'],
    ];

    /** @test */
    public function getCharacterForEachRegionWhichIsntPresentInDatabase()
    {
        foreach ($this->characters as $character) {
            \Log::info('Fetching character', $character);

            $character = (object)$character;

            Character::where([
                'name' => $character->name,
                'realm' => $character->realm,
                'region' => $character->region
            ])->delete();

            $this
                ->get("/api/character/$character->region/$character->realm/$character->name")
                ->assertStatus(200);
        }
    }

    /**
     * @test
     * @depends getCharacterForEachRegionWhichIsntPresentInDatabase
     */
    public function getCharacterForEachRegionWhichAlreadyPresentInDatabase()
    {
        foreach ($this->characters as $character) {
            \Log::info('Fetching character', $character);

            $character = (object)$character;

            $this
                ->get("/api/character/$character->region/$character->realm/$character->name")
                ->assertStatus(200);
        }
    }

    /** @test */
    public function nonExistentCharacterGives404()
    {
        $this
            ->get('/api/character/eu/randomRealm/randomCharacter')
            ->assertNotFound();
    }

}
