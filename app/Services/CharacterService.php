<?php


namespace App\Services;

use App\DTO\Character\BlizzardCharacterData;
use App\DTO\Character\CharacterBasic;
use App\DTO\Character\CharacterDocument;
use App\DTO\Character\Item;
use App\DTO\Character\Media;
use App\DTO\Character\Specialization;
use App\Models\Character;
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

    public function getBasicCharacterInfo(string $region, string $realmName, string $characterName, string $ownerId = null): Character
    {
        $realmName = Str::slug($realmName);
        $characterName = strtolower($characterName);

        $character = Character::where([
            'name' => $characterName,
            'realm' => $realmName,
            'region' => $region
        ])->first();

        if (
            !$character ||
            $character->updated_at->diffInSeconds() > config('blizzard.character_min_seconds_update')
        ) {
            $responses = $this->profileClient->getCharacterInfo($region, $realmName, $characterName);

            $characterDocument = new CharacterDocument([
                'name' => $characterName,
                'realm' => $realmName,
                'region' => $region,
                'user_id' => $ownerId,
                'num_of_searches' => optional($character)->num_of_searches ? $character->num_of_searches++ : 0,
                'basic' => $this->mapBasicResponseData($responses['basic']),
                'media' => $this->mapMediaResponseData($responses['media']),
                'equipment' => $this->mapEquipmentResponseData($responses['equipment']),
                'specialization' => $this->mapSpecializationsResponseData($responses['specialization'])
            ]);

            $character = Character::updateOrCreate([
                'name' => $characterName,
                'realm' => $realmName,
                'region' => $region
            ],
                $characterDocument->toArray()
            );
        }

        return $character;
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

    private function mapBasicResponseData(Response $response): CharacterBasic
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

        return new CharacterBasic($result);
    }

    private function mapMediaResponseData(Response $response): Media
    {
        $data = json_decode($response->getBody());

        $pictures = [];

        if (isset($data->assets)) {
            foreach ($data->assets as $asset) {
                $pictures[$asset->key] = $asset->value;
            }
        } else {
            $pictures = [
                'avatar' => $data->avatar_url,
                'inset' => $data->bust_url,
                'main' => $data->render_url
            ];
        }

        return new Media($pictures);
    }

    /** @return Item[] */
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

    private function mapSpecializationsResponseData(Response $response): Specialization
    {
        $data = json_decode($response->getBody());

        $activeSpecName = $data->active_specialization->name;

        $activeSpec = collect($data->specializations)
            ->firstWhere('specialization.name', $activeSpecName);

        $talents = [];
        if (!empty($activeSpec->talents)) {
            $talents = collect($activeSpec->talents)
                ->map(fn($talent) => $talent->spell_tooltip->spell->id)
                ->toArray();
        }

        return new Specialization([
            'activeSpecialization' => $activeSpecName,
            'talents' => $talents
        ]);
    }

}
