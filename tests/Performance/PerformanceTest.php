<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PerformanceTest extends TestCase
{

    /** @test */
    public function getGuildRosterParallel()
    {
        $client = new Client([
            'base_uri' => 'https://guild-service-be.herokuapp.com/api/'
        ]);

        $guilds = [
            ['name' => 'alterac-deviants', 'locale' => 'eu', 'realm' => 'twisting-nether'],
            ['name' => 'complexity-limit', 'locale' => 'us', 'realm' => 'illidan'],
            ['name' => 'pieces', 'locale' => 'eu', 'realm' => 'draenor'],
//            ['name' => 'method', 'locale' => 'eu', 'realm' => 'tarren-mill'],
            ['name' => 'Aversion', 'locale' => 'eu', 'realm' => 'blackhand'],
            ['name' => 'walkthrough', 'locale' => 'eu', 'realm' => 'kazzak'],
        ];

        $CHARACTER_CHUNK_SIZE = 10;

        foreach ($guilds as $guild) {
            $response = $client->get($this->getUrlForGuild($guild['realm'], $guild['name'], $guild['locale']));
            $roster = json_decode($response->getBody())->guild->roster;

            $counter = 1;
            $chunks = collect($roster)->chunk($CHARACTER_CHUNK_SIZE);

            foreach ($chunks as $character) {

                $urls = [];
                foreach($character as $member) {
                    array_push($urls, $this->getUrlForCharacter($member->realm, $member->name, $member->region));
                }

                Log::info("Built URLs for chunk no $counter", $urls);

                $promises = [];
                foreach($urls as $url) {
                    array_push($promises, $client->getAsync($url));
                }

                try {
                    Promise\unwrap($promises);
                } catch (\Exception $e) {
                    Log::info("Exception happened when unwrapping promises");
                }
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
