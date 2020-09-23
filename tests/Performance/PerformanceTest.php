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
            ['name' => 'alterac-deviants', 'region' => 'eu', 'realm' => 'twisting-nether'],
            ['name' => 'complexity-limit', 'region' => 'us', 'realm' => 'illidan'],
            ['name' => 'pieces', 'region' => 'eu', 'realm' => 'draenor'],
//            ['name' => 'method', 'region' => 'eu', 'realm' => 'tarren-mill'],
            ['name' => 'Aversion', 'region' => 'eu', 'realm' => 'blackhand'],
            ['name' => 'walkthrough', 'region' => 'eu', 'realm' => 'kazzak'],
        ];

        $CHARACTER_CHUNK_SIZE = 10;

        foreach ($guilds as $guild) {
            $response = $client->get($this->getUrlForGuild($guild['realm'], $guild['name'], $guild['region']));
            $roster = json_decode($response->getBody())->guild->guild_data->roster->members;

            $counter = 1;
            $chunks = collect($roster)->chunk($CHARACTER_CHUNK_SIZE);

            foreach ($chunks as $character) {

                $urls = [];
                foreach($character as $member) {
                    array_push($urls, $this->getUrlForCharacter($member->character->realm->slug, $member->character->name, $guild['region']));
                }

                Log::info("Built URLs for chunk no $counter", $urls);

                $promises = [];
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

    private function getUrlForGuild($realm, $name, $region)
    {
        return "guild/" . $region . "/" . $realm . "/" . $name;
    }

    private function getUrlForCharacter($realm, $name, $region)
    {
        return "character/" . $region . "/" . $realm . "/" . $name;
    }

}
