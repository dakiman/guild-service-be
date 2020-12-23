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
//            'base_uri' => 'https://guild-service-be.herokuapp.com/api/'
            'base_uri' => '127.0.0.1:8000/api/'
        ]);

        $guilds = [
            ['name' => 'Practice', 'region' => 'eu', 'realm' => 'ragnaros'],
            ['name' => 'Aversion', 'region' => 'eu', 'realm' => 'blackhand'],
            ['name' => 'alterac-deviants', 'region' => 'eu', 'realm' => 'twisting-nether'],
            ['name' => 'Memento', 'region' => 'eu', 'realm' => 'stormreaver'],
            ['name' => 'walkthrough', 'region' => 'eu', 'realm' => 'kazzak'],
            ['name' => 'pieces', 'region' => 'eu', 'realm' => 'draenor'],
//            ['name' => 'method', 'region' => 'eu', 'realm' => 'tarren-mill'],
            ['name' => 'complexity-limit', 'region' => 'us', 'realm' => 'illidan'],
        ];

        $CHARACTER_CHUNK_SIZE = 10;

        foreach ($guilds as $guild) {
            $response = $client->get($this->getUrlForGuild($guild['realm'], $guild['name'], $guild['region']));
            $roster = json_decode($response->getBody())->guild->roster;

            $counter = 1;
            $chunks = collect($roster)->chunk($CHARACTER_CHUNK_SIZE);

            foreach ($chunks as $character) {

                $urls = [];
                foreach($character as $member) {
                    array_push($urls, $this->getUrlForCharacter(strtolower($member->realm), strtolower($member->name), strtolower($guild['region'])));
                }

                Log::info("Built URLs for chunk no $counter", $urls);

                $counter = 0;
                while($counter < 5) {
                    $counter++;
                    $promises = [];

                    foreach($urls as $url) {
                        array_push($promises, $client->getAsync($url));
                        $client->getAsync($this->getUrlForGuild($guild['realm'], $guild['name'], $guild['region']));
                    }

                    try {
                        Promise\unwrap($promises);
                    } catch (\Throwable $e) {
                        Log::info("Exception happened when unwrapping promises", [$e->getMessage()]);
                    }
                }

            }
        }
    }

    private function getUrlForGuild($realm, $name, $region)
    {
        return "guild/" . $region . "/" . $realm . "/" . $name;
    }

    private function getUrlForCharacter($realm, $name, $region)
    {
        return "character/" . $region . "/" . $realm . "/" . $name;
    }

    public function testPerformance()
    {
        $client = new Client([
            'base_uri' => 'https://guild-service-be.herokuapp.com/api/'
        ]);

        while(true) {
            $promises = [];

            $urls = [
              $this->getUrlForCharacter('the-maelstrom', 'daki', 'eu'),
              $this->getUrlForCharacter('the-maelstrom', 'sernaos', 'eu'),
              $this->getUrlForCharacter('the-maelstrom', 'dakix', 'eu'),
              $this->getUrlForCharacter('the-maelstrom', 'stabber', 'eu'),
              $this->getUrlForCharacter('the-maelstrom', 'daki', 'eu'),
            ];

            foreach($urls as $url) {
                array_push($promises, $client->getAsync($url));
            }

            try {
                Promise\unwrap($promises);
            } catch (\Throwable $e) {
                Log::info("Exception happened when unwrapping promises", $e->getTrace());
            }
        }
    }

}
