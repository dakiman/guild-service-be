<?php

namespace App\Services\Blizzard;

use App\Exceptions\BlizzardServiceException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class BlizzardProfileClient
{
    /*
     * @return array [
     *      'basic' => GuzzleHttp\Psr7\Response,
     *      'roster' => GuzzleHttp\Psr7\Response,
     *      'achievements' => GuzzleHttp\Psr7\Response
     *  ]
     * */
    public function getGuildInfo(string $region, string $realmName, string $guildName)
    {
        $client = $this->buildClientForRegion($region);

        $promises = [
            'basic' => $client->getAsync("/data/wow/guild/$realmName/$guildName"),
            'roster' => $client->getAsync("/data/wow/guild/$realmName/$guildName/roster"),
            'achievements' => $client->getAsync("/data/wow/guild/$realmName/$guildName/achievements")
        ];

        try {
            return Promise\unwrap($promises);
        } catch (Exception $e) {
            throw new BlizzardServiceException('Couldnt retrieve guild', $e, 404);
        }
    }

    /*
    * @return array [
    *      'basic' => GuzzleHttp\Psr7\Response,
    *      'media' => GuzzleHttp\Psr7\Response,
    *      'equipment' => GuzzleHttp\Psr7\Response
    *  ]
    * */
    public function getCharacterInfo(string $region, string $realmName, string $characterName)
    {
        $client = $this->buildClientForRegion($region);

        $promises = [
            'basic' => $client->getAsync("/profile/wow/character/$realmName/$characterName"),
            'media' => $client->getAsync("/profile/wow/character/$realmName/$characterName/character-media"),
            'equipment' => $client->getAsync("/profile/wow/character/$realmName/$characterName/equipment")
        ];

        try {
            return Promise\unwrap($promises);
        } catch (Exception $e) {
            throw new BlizzardServiceException("Couldnt retrieve character $characterName @ $realmName | $region", $e, 404);
        }
    }
//
//    public function getUserAccountData(string $token, string $region)
//    {
//        $client = $this->buildClientForRegion($region);
//
//        $response = $client->get('/profile/user/wow?access_token=' . $token);
//
//        return json_decode($response->getBody());
//    }


    private function buildClientForRegion(string $region)
    {
//        $region = strtolower($region);
        $apiUrl = str_replace('{region}', $region, config('blizzard.api.url'));

        $authClient = app(BlizzardAuthClient::class, ['region' => $region]);

        return new Client([
            'headers' => ['Authorization' => 'Bearer ' . $authClient->retrieveToken()],
            'base_uri' => $apiUrl,
            'query' => [
                'namespace' => 'profile-' . $region,
                'locale' => 'en_GB'
            ]
        ]);
    }

}
