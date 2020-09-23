<?php

namespace Tests\Feature;

use App\Character;
use Tests\TestCase;

class GuildTest extends TestCase
{

    /** @test */
    public function getMostPopularGuilds()
    {
        $this
            ->get('/api/guild/popular')
            ->assertJsonStructure(['recently_searched', 'most_popular'])
            ->assertStatus(200);
    }

}
