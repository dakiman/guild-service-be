<?php

namespace App\Services\Blizzard;

use App\Exceptions\BlizzardServiceException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class BlizzardProfileClient
{
    private Client $client;

    public function __construct(BlizzardAuthClient $authClient)
    {
        $this->client = new Client([
            'headers' => ['Authorization' => 'Bearer ' . $authClient->retrieveToken() ],
            'base_uri' => 'https://' . strtolower(config('locale')) .'.api.blizzard.com/data/wow/',
            'query' => [
                'namespace' => 'profile-' . strtolower(config('locale')),
                'locale' => 'en_' . strtoupper(config('locale'))
            ]
        ]);
    }

    public function getGuildBasicInfo(string $realmName, string $guildName)
    {
        try {
            return $this->client->get("guild/$realmName/$guildName");
        } catch (BadResponseException $e) {
            throw new BlizzardServiceException('Couldnt retrieve guild with status ' . $e->getResponse()->getStatusCode());
        }
    }

    public function getGuildRoster(string $realmName, string $guildName)
    {
        try {
            return $this->client->get("guild/$realmName/$guildName/roster");
        } catch (BadResponseException $e) {
            throw new BlizzardServiceException('Couldnt retrieve guild with status ' . $e->getResponse()->getStatusCode());
        }
    }

    public function getGuildAchievements(string $realmName, string $guildName)
    {
        try {
            return $this->client->get("guild/$realmName/$guildName/achievements");
        } catch (BadResponseException $e) {
            throw new BlizzardServiceException('Couldnt retrieve guild achievements with status ' . $e->getResponse()->getStatusCode());
        }
    }

}
