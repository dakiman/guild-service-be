<?php

namespace Tests\Feature;

use App\Jobs\RetrieveGuildRoster;
use App\Jobs\RetrieveMythicDungeonData;
use App\Models\Character;
use App\Exceptions\BlizzardServiceException;
use App\Models\Guild;
use App\Services\DungeonService;
use Tests\TestCase;

class DungeonTest extends TestCase
{
    private $characters = [
        ['name' => 'Sernaos', 'realm' => 'the-maelstrom', 'region' => 'eu'],
        ['name' => 'Vilwarr', 'realm' => 'illidan', 'region' => 'us'],
    ];

    /** @test */
    public function getCharacterForEachRegionWhichIsntPresentInDatabase()
    {
       $dungeonService = app(DungeonService::class);
       $character = Character::first();
       $dungeonService->getMythicDungeonData($character);
    }

    /** @test */
    public function getTest()
    {
        RetrieveMythicDungeonData::dispatch(Character::first());
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

    /** @test */
    public function getCharacter()
    {
        $this
            ->get('/api/character/eu/the-maelstrom/daki')
            ->assertOk();
    }

}
