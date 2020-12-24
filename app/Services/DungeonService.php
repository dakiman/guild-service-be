<?php


namespace App\Services;


use App\Models\Character;
use App\Models\DungeonRun;
use App\Services\Blizzard\BlizzardProfileClient;
use Log;

class DungeonService
{
    private BlizzardProfileClient $profileClient;

    public function __construct(BlizzardProfileClient $profileClient)
    {
        $this->profileClient = $profileClient;
    }

    public function getMythicDungeonData(Character $character)
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

            /*Check if the firstOrCreate method returned an old instance or created a new one*/
            if ($dungeonRun->wasRecentlyCreated) {
                $runs = $character->dungeon_runs;

                if(!$runs) $runs = [];

                if (!array_key_exists($season, $runs)) $runs[$season] = [];

                if (!array_key_exists($dungeonRun->dungeon['name'], $runs[$season])) $runs[$season][$dungeonRun->dungeon['name']] = [];


                array_push($runs[$season][$dungeonRun->dungeon['name']], $dungeonRun->toArray());
                $character->dungeon_runs = $runs;
                $character->save();
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
//            RetrieveCharacterData::dispatch($character->region, $member->character->realm->slug, $member->character->name);

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
