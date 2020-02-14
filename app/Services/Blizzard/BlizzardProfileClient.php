<?php

namespace App\Services\Blizzard;

use App\Exceptions\BlizzardServiceException;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

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

    public function getGuildInfo(string $realmName, string $guildName): array
    {
        $promises = [
            'basic' => $this->client->getAsync("/data/wow/guild/$realmName/$guildName"),
            'roster' => $this->client->getAsync("/data/wow/guild/$realmName/$guildName/roster"),
            'achievements' => $this->client->getAsync("/data/wow/guild/$realmName/$guildName/achievements")
        ];

        try {
            return Promise\unwrap($promises);
        } catch (\Exception $e) {
            throw new BlizzardServiceException('Couldnt retrieve guild', $e);
        }
    }

    public function getCharacterInfo(string $realmName, string $characterName)
    {
        $promises = [
            'basic' => $this->client->getAsync("/profile/wow/character/$realmName/$characterName"),
            'media' => $this->client->getAsync("/profile/wow/character/$realmName/$characterName/character-media")
        ];

        try {
            return Promise\unwrap($promises);
        } catch (\Exception $e) {
            throw new BlizzardServiceException('Couldnt retrieve character', $e);
        }
    }

}
