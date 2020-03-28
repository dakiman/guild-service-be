<?php


namespace App\Services\Blizzard;

use App\Exceptions\BlizzardServiceException;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class BlizzardUserClient
{
    private string $oauthUrl;
    private string $apiUrl;

    public function __construct($region)
    {
        $this->oauthUrl = str_replace('{region}', $region, config('blizzard.oauth.url'));
        $this->apiUrl = str_replace('{region}', $region, config('blizzard.api.url'));
    }

    public function getUserInfoAndCharacters(string $token, string $region)
    {
        $promises = [
            'oauth' => $this->getUserInfo($token, $region),
            'characters' => $this->getUserCharacters($token, $region)
        ];

        try {
            return Promise\unwrap($promises);
        } catch (Exception $e) {
            throw new BlizzardServiceException('Had issues finishing OAuth process.', $e, 404);
        }
    }

    private function getUserInfo(string $token, string $region)
    {
        $client = $this->buildClient($token, $region);

        return $client->getAsync($this->oauthUrl . '/oauth/userinfo');
    }

    private function getUserCharacters(string $token, string $region)
    {
        $client = $this->buildClient($token, $region);

        return $client->getAsync($this->apiUrl . '/profile/user/wow');
    }

    private function buildClient($token, $region)
    {
        return new Client([
//            'base_uri' => $this->oauthUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ],
            'query' => [
                'namespace' => 'profile-' . $region,
                'locale' => 'en_GB'
            ]
        ]);
    }


}
