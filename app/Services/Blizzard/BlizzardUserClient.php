<?php


namespace App\Services\Blizzard;

use App\Exceptions\BlizzardServiceException;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class BlizzardUserClient
{
    private string $oauthUrl;
    private string $apiUrl;

    public function __construct($locale)
    {
        $this->oauthUrl = str_replace('{locale}', $locale, config('blizzard.oauth.url'));
        $this->apiUrl = str_replace('{locale}', $locale, config('blizzard.api.url'));
    }

    public function getUserInfoAndCharacters(string $token, string $locale)
    {
        $promises = [
            'oauth' => $this->getUserInfo($token, $locale),
            'characters' => $this->getUserCharacters($token, $locale)
        ];

        try {
            return Promise\unwrap($promises);
        } catch (Exception $e) {
            throw new BlizzardServiceException('Had issues finishing OAuth process.', $e, 404);
        }
    }

    private function getUserInfo(string $token, string $locale)
    {
        $client = $this->buildClient($token, $locale);

        return $client->getAsync($this->oauthUrl . '/oauth/userinfo');
    }

    private function getUserCharacters(string $token, string $locale)
    {
        $client = $this->buildClient($token, $locale);

        return $client->getAsync($this->apiUrl . '/profile/user/wow');
    }

    private function buildClient($token, $locale)
    {
        return new Client([
//            'base_uri' => $this->oauthUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ],
            'query' => [
                'namespace' => 'profile-' . $locale,
                'locale' => 'en_GB'
            ]
        ]);
    }


}
