<?php


namespace App\Services;

use App\DTO\Character\Basic;
use App\DTO\Character\CharacterDocument;
use App\DTO\Character\Item;
use App\DTO\Character\Media;
use App\Models\Character;
use App\Exceptions\BlizzardServiceException;
use App\Services\Blizzard\BlizzardProfileClient;
use GuzzleHttp\Psr7\Response;
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

        $blizzardData['basic'] = new Basic($this->mapBasicResponseData($responses['basic']));
        $blizzardData['media'] = new Media($this->mapMediaResponseData($responses['media']));
        $blizzardData['equipment'] = $this->mapEquipmentResponseData($responses['equipment']);

        return $blizzardData;
    }

    private static function mapBasicResponseData(Response $response)
    {
        $data = json_decode($response->getBody());

        $result = [
            'gender' => $data->gender->name,
            'faction' => $data->faction->name,
            'race' => $data->race->id,
            'class' => $data->character_class->id,
            'level' => $data->level,
            'achievement_points' => $data->achievement_points,
            'average_item_level' => $data->average_item_level,
            'equipped_item_level' => $data->equipped_item_level,
        ];

        if (isset($data->guild)) {
            $result['guild'] = [
                'name' => $data->guild->name,
                'realm' => $data->guild->realm->name,
                'faction' => $data->guild->faction->name ?? null
            ];
        }

        return $result;
    }

    private static function mapMediaResponseData(Response $response)
    {
        $data = json_decode($response->getBody());

        $pictures = [];

        if (isset($data->assets)) {
            foreach ($data->assets as $asset) {
                $pictures[$asset->key] = $asset->value;
            }
        } else {
            return [
                'avatar' => $data->avatar_url,
                'inset' => $data->bust_url,
                'main' => $data->render_url
            ];
        }

        return $pictures;
    }

    private function mapEquipmentResponseData(Response $response)
    {
        $data = json_decode($response->getBody());

        $equipment = [];

        foreach ($data->equipped_items as $equipped) {
            $item = new Item([
                'id' => $equipped->item->id,
                'itemLevel' => $equipped->level->value,
                'quality' => $equipped->quality->name
            ]);
            array_push($equipment, $item);
        }

        return $equipment;
    }

}
