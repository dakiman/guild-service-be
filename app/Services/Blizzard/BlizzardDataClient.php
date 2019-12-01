<?php
/**
 * Created by PhpStorm.
 * User: Daki
 * Date: 12/1/2019
 * Time: 7:42 PM
 */

namespace App\Services\Blizzard;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class BlizzardDataClient
{
    /** @var Client */
    private $client;

    public function __construct(BlizzardAuthClient $authClient)
    {
        $this->client = new Client([
            'headers' => ['Authorization' => 'Bearer ' . $authClient->retrieveToken() ],
            'base_uri' => 'https://eu.api.blizzard.com/data/wow/',
            'query' => [
                'namespace' => 'profile-eu',
                'locale' => 'en_EU'
            ]
        ]);
    }

    public function getGuildRoster(string $realmName, string $guildName): Response
    {
        return $this->client->get("guild/$realmName/$guildName/roster");
    }

    public function getGuildAchievements(string $realmName, string $guildName): Response
    {
        return $this->client->get("guild/$realmName/$guildName/achievements");
    }

}
