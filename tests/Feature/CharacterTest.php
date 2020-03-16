<?php

namespace Tests\Feature;

use App\Character;
use App\Exceptions\BlizzardServiceException;
use Tests\TestCase;

class CharacterTestCase extends TestCase
{
    private $characters = [
        ['name' => 'Sernaos', 'realm' => 'the-maelstrom', 'locale' => 'eu'],
        ['name' => 'Vilwarr', 'realm' => 'illidan', 'locale' => 'us'],
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
                'region' => $character->locale
            ])->delete();

            $this
                ->get("/api/character/$character->realm/$character->name?locale=$character->locale")
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
                ->get("/api/character/$character->realm/$character->name?locale=$character->locale")
                ->assertStatus(200);
        }
    }

    /** @test */
    public function nonExistentCharacterGives404()
    {
        $this->expectException(BlizzardServiceException::class);
        $this
            ->get('/api/character/randoBoy/randoRealm?locale=eu')
            ->assertStatus(404);
    }
}
