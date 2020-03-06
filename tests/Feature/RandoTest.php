<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RandoTest extends TestCase
{

    /** @test */
    public function rando()
    {
        $response = $this->get('/api/character/The%20Maelstrom/Snorlâx?locale=eu');

        $response->assertStatus(200);
    }

}
