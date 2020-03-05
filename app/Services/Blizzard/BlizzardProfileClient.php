<?php

namespace App\Services\Blizzard;

use App\Exceptions\BlizzardServiceException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class BlizzardProfileClient extends BaseBlizzardClient
{
    /*
     * @return array [
     *      'basic' => GuzzleHttp\Psr7\Response,
     *      'roster' => GuzzleHttp\Psr7\Response,
     *      'achievements' => GuzzleHttp\Psr7\Response
     *  ]
     * */
    public function getGuildInfo(string $realmName, string $guildName, string $locale)
    {
        $client = $this->buildClientForRegion($locale);

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
    public function getCharacterInfo(string $realmName, string $characterName, string $locale)
    {
        $client = $this->buildClientForRegion($locale);

        $promises = [
            'basic' => $client->getAsync("/profile/wow/character/$realmName/$characterName"),
            'media' => $client->getAsync("/profile/wow/character/$realmName/$characterName/character-media"),
            'equipment' => $client->getAsync("/profile/wow/character/$realmName/$characterName/equipment")
        ];

        try {
            return Promise\unwrap($promises);
        } catch (Exception $e) {
            throw new BlizzardServiceException('Couldnt retrieve character', $e, 404);
        }
    }

    private function buildClientForRegion(string $locale)
    {
        $locale = strtolower($locale);
        $apiUrl = str_replace('{locale}', $locale, config('blizzard.api.url'));

        return new Client([
            'headers' => ['Authorization' => 'Bearer ' . $this->retrieveToken()],
            'base_uri' => $apiUrl,
            'query' => [
                'namespace' => 'profile-' . $locale,
                'locale' => 'en_GB'
            ]
        ]);
    }

}
