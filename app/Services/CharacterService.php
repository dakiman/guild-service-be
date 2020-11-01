<?php


namespace App\Services;

use App\DTO\Character\Basic;
use App\DTO\Character\BlizzardData;
use App\DTO\Character\CharacterDocument;
use App\DTO\Character\Item;
use App\DTO\Character\Media;
use App\Models\Character;
use App\Exceptions\BlizzardServiceException;
use App\Services\Blizzard\BlizzardProfileClient;
use phpDocumentor\Reflection\Types\Object_;
use Str;

class CharacterService
{
    private BlizzardProfileClient $profileClient;

    public function __construct(BlizzardProfileClient $profileClient)
    {
        $this->profileClient = $profileClient;
    }

    public function getBasicCharacterInfo(string $region, string $realmName, string $characterName, string $ownerId = null): CharacterDocument
    {
        $realmName = Str::slug($realmName);
        $characterName = strtolower($characterName);

        $character = Character::where([
            'name' => $characterName,
            'realm' => $realmName,
            'region' => $region
        ])->first();

        if ($character) {
            if ($ownerId != null) {
                $character->user_id = $ownerId;
            }
            $character->increasePopularity();
            $character->save();
        } else {
            $characterDocument = CharacterDocument::fromArray([
                'name' => $characterName,
                'realm' => $realmName,
                'region' => $region,
                'user_id' => $ownerId,
                'blizzard_data' => $this->getCharacterData($region, $realmName, $characterName)
            ]);
            $character = Character::create($characterDocument->toArray());
        }
        return $character->toDTO();
    }

    public function retrieveCharactersFromAccount($token, $region)
    {
        $accountData = $this->profileClient->getUserCharacters($token, $region);
        $characters = $accountData->wow_accounts[0]->characters;

        $savedCharacters = [];
        $ownerId = auth()->user()->id;

        foreach ($characters as $character) {
            try {
                $singleCharacter = $this->getBasicCharacterInfo(
                    $region, $character->realm->slug, $character->name, $ownerId
                );

                array_push($savedCharacters, $singleCharacter);
            } catch (BlizzardServiceException $e) {
                continue;
            }

        }

        return $savedCharacters;
    }

    public function getRecentlySearched()
    {
        return Character
            ::orderBy('updated_at', 'desc')
            ->limit(5)
            ->get(['name', 'region', 'realm']);
    }

    public function getMostPopular()
    {
        return Character
            ::orderBy('num_of_searches', 'desc')
            ->limit(5)
            ->get(['name', 'region', 'realm']);
    }

    private function getCharacterData(string $region, string $realmName, string $characterName)
    {
        $responses = $this->profileClient->getCharacterInfo($region, $realmName, $characterName);

        $blizzardData['basic'] = Basic::fromResponse(json_decode($responses['basic']->getBody()));
        $blizzardData['media'] = Media::fromResponse(json_decode($responses['media']->getBody()));
//        $blizzardData['equipment'] = Item::fromResponse(json_decode($responses['equipment']->getBody()));

        return $blizzardData;
    }


}
