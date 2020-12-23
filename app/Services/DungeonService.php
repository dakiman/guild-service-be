<?php


namespace App\Services;


use App\Jobs\RetrieveCharacterData;
use App\Models\Character;
use App\Models\DungeonRun;
use App\Services\Blizzard\BlizzardProfileClient;

class DungeonService
{
    private BlizzardProfileClient $profileClient;

    public function __construct(BlizzardProfileClient $profileClient)
    {
        $this->profileClient = $profileClient;
    }

    public function
    getMythicDungeonData(Character $character)
    {
        $responses = $this->profileClient->getBestMythicsInfo($character->region, $character->realm, $character->name);

        $this
            ->parseDungeonData($responses['best_mythics'], $character);
    }

    private function parseDungeonData($response, $character)
    {
        $data = json_decode($response->getBody());

        $season = $data->season->id;
        foreach ($data->best_runs as $runData) {
            $dungeonRun = DungeonRun::firstOrCreate([
                'season' => $season,
                'dungeon' => [
                    'name' => $runData->dungeon->name,
                    'id' => $runData->dungeon->id
                ],
                'onTime' => $runData->is_completed_within_time,
                'completedTimestamp' => $runData->completed_timestamp,
                'duration' => $runData->duration,
                'keystoneLevel' => $runData->keystone_level,
                'affixes' => $this->parseAffixes($runData->keystone_affixes),
                'team' => $this->parseTeam($runData->members, $character)
            ]);

            if ($dungeonRun->wasRecentlyCreated) {
                $character->dungeonRuns()->create($dungeonRun->toArray());
            }

        }
    }

    private function parseAffixes($affixes)
    {
        return array_map(fn($affix) => ['name' => $affix->name, 'id' => $affix->id], $affixes);
    }

    /*TODO Create character jobs*/
    private function parseTeam($members, $character)
    {
        $team = [];
        foreach ($members as $member) {
            RetrieveCharacterData::dispatch($character->region, $member->character->realm->slug, $member->character->name);

            array_push($team, [
                'name' => $member->character->name,
                'realm' => $member->character->realm->slug,
                'region' => $character->region,
                'spec' => [
                    'id' => $member->specialization->id,
                    'name' => $member->specialization->name,
                ],
                'equippedItemLevel' => $member->equipped_item_level
            ]);
        }

        return $team;
    }


}
