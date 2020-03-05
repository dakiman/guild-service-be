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
        $response = $this->get('/api/character/the-maelstrom/sernaos?locale=eu');

        $response->assertStatus(200);
    }

}
