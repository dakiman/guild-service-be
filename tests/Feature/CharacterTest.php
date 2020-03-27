<?php

namespace Tests\Feature;

use App\Character;
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
//        TODO FIX
//        $this->expectException(BlizzardServiceException::class);
        $this
            ->get('/api/character/randoBoy/randoRealm?locale=eu')
            ->assertStatus(404);
    }

    /** @test */
    public function adasd()
    {
        $response = $this
            ->post('/api/blizzard-oauth?locale=EU', ['code' => 'EUJW9XLFK2VBU2OWTQX7D811ZDKTCCKZGS', 'redirectUri' => 'http://localhost:8080/blizzard-oauth?locale=eu']);

        dd($response->getContent());
    }

}
