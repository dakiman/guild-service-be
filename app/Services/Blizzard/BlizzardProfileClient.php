<?php

namespace App\Services\Blizzard;

use App\Exceptions\BlizzardServiceException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class BlizzardProfileClient
{
    private Client $client;

    public function __construct(BlizzardAuthClient $authClient, $locale)
    {
        $locale = strtolower($locale);
        $apiUrl = str_replace('{locale}', $locale, config('blizzard.api.url'));

        $this->client = new Client([
            'headers' => ['Authorization' => 'Bearer ' . $authClient->retrieveToken()],
            'base_uri' => $apiUrl,
            'query' => [
                'namespace' => 'profile-' . $locale,
                'locale' => 'en_' . $locale
            ]
        ]);
    }

    public function getGuildBasicInfo(string $realmName, string $guildName)
    {
        try {
            return $this->client->get("/data/wow/guild/$realmName/$guildName");
        } catch (BadResponseException $e) {
            throw new BlizzardServiceException('Couldnt retrieve guild with status ' . $e->getResponse()->getStatusCode());
        }
    }

    public function getGuildRoster(string $realmName, string $guildName)
    {
        try {
            return $this->client->get("/data/wow/guild/$realmName/$guildName/roster");
        } catch (BadResponseException $e) {
            throw new BlizzardServiceException('Couldnt retrieve guild with status ' . $e->getResponse()->getStatusCode());
        }
    }

    public function getGuildAchievements(string $realmName, string $guildName)
    {
        try {
            return $this->client->get("/data/wow/guild/$realmName/$guildName/achievements");
        } catch (BadResponseException $e) {
            throw new BlizzardServiceException('Couldnt retrieve guild achievements with status ' . $e->getResponse()->getStatusCode());
        }
    }

    public function getCharacterBasicInfo(string $realmName, string $characterName)
    {
        try {
            return $this->client->get("/profile/wow/character/$realmName/$characterName");
        } catch (BadResponseException $e) {
            throw new BlizzardServiceException('Couldnt retrieve character data with status ' . $e->getResponse()->getStatusCode());
        }
    }

    public function getCharacterMedia(string $realmName, string $characterName)
    {
        try {
            return $this->client->get("/profile/wow/character/$realmName/$characterName/character-media");
        } catch (BadResponseException $e) {
            throw new BlizzardServiceException('Couldnt retrieve character data with status ' . $e->getResponse()->getStatusCode());
        }
    }

}
