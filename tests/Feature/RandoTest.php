<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RandoTest extends TestCase
{

    /** @test */
    public function rando()
    {
        $client = new Client([
            'base_uri' => 'https://guild-service-be.herokuapp.com/api/'
        ]);

        $guilds = [
            ['name' => 'complexity-limit', 'locale' => 'us', 'realm' => 'illidan'],
            ['name' => 'method', 'locale' => 'eu', 'realm' => 'tarren-mill'],
            ['name' => 'Aversion', 'locale' => 'eu', 'realm' => 'blackhand'],
        ];

        foreach ($guilds as $guild) {
            $response = $client->get($this->getUrlForGuild($guild['realm'], $guild['name'], $guild['locale']));
            $roster = json_decode($response->getBody())->guild->roster;

            $counter = 1;
            $chunks = collect($roster)->chunk(5);

            foreach ($chunks as $chunk) {
                Log::info("\n Now doing request no $counter for guild " . $guild['name']);
                $counter++;

                $urls = [];
                foreach($chunk as $member) {
                    array_push($urls, $this->getUrlForCharacter($member->realm, $member->name, $member->region));
                }

                $promises = [];
                foreach($urls as $url) {
                    array_push($promises, $client->getAsync($url));
                }

                try {
                    Promise\unwrap($promises);
                } catch (\Exception $e) {
                    Log::info("\n Exception  happened when unwrapping promises");
                }

                Log::info("================================");
            }
        }
    }

    private function getUrlForGuild($realm, $name, $locale)
    {
        return "guild/" . $realm . "/" . $name . "?locale=" . $locale;
    }

    private function getUrlForCharacter($realm, $name, $locale)
    {
        return "character/" . $realm . "/" . $name . "?locale=" . $locale;
    }

}
