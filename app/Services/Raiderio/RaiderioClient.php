<?php


namespace App\Services\Raiderio;

use App\Exceptions\RaiderioServiceException;
use GuzzleHttp\Client;

class RaiderioClient
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://raider.io/api/v1/'
        ]);
    }

    public function getRaiderioInfo(string $realmName, string $characterName, string $region)
    {
        try {
            return $this->client->get('characters/profile', [
                'query' => [
                    'region' => $region,
                    'realm' => $realmName,
                    'name' => $characterName,
                    'fields' => 'raid_progression,mythic_plus_ranks,gear'
                ]
            ]);
        } catch (\Exception $e) {
            throw new RaiderioServiceException('Couldnt retrieve character data from RaiderIO services.', $e, 404);
        }
    }
}
