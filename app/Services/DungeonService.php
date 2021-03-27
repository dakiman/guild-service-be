<?php


namespace App\Services;


use App\Jobs\RetrieveCharacterData;
use App\Models\Character;
use App\Models\DungeonRun;
use App\Services\Blizzard\BlizzardProfileClient;
use Log;

class DungeonService
{
    private BlizzardProfileClient $profileClient;
    private CharacterService $characterService;

    public function __construct(BlizzardProfileClient $profileClient, CharacterService $characterService)
    {
        $this->profileClient = $profileClient;
        $this->characterService = $characterService;
    }

    public function getMythicDungeonData(Character $character)
    {
        $responses = $this->profileClient->getBestMythicsInfo($character->region, $character->realm, $character->name);

        $this
            ->parseDungeonData($responses['best_mythics'], $character);

        $character->mythics_synced_at = now()->toDateTimeString();
        $character->save();
    }

    private function parseDungeonData($response, $character)
    {
        $data = json_decode($response->getBody());

        $season = $data->season->id;

        foreach ($data->best_runs as $runData) {
            $team = $this->parseTeam($runData->members, $character);

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
                'team' => $team
            ]);

            /*Check if the firstOrCreate method returned an old instance or created a new one*/
            if ($dungeonRun->wasRecentlyCreated) {
                $this->addDungeonToTeam($dungeonRun, $team);
            }

        }
    }

    private function parseAffixes($affixes)
    {
        return array_map(fn($affix) => ['name' => $affix->name, 'id' => $affix->id], $affixes);
    }

    private function parseTeam($members, $character)
    {
        $team = [];
        foreach ($members as $member) {
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

    private function addDungeonToTeam($dungeonRun, array $team)
    {
        foreach($team as $teamMember) {
            try {
                $character = $this->characterService->getCharacter($teamMember['region'], $teamMember['realm'], $teamMember['name']);
            } catch (\Exception $e) {
                Log::error('Error while retrieving character from Mythic run', ['member' => $teamMember, 'run' => $dungeonRun]);
                continue;
            }

            $runs = $character->dungeon_runs;

            if(!$runs) $runs = [];

            if (!array_key_exists($dungeonRun->season, $runs)) $runs[$dungeonRun->season] = [];

            if (!array_key_exists($dungeonRun->dungeon['name'], $runs[$dungeonRun->season])) $runs[$dungeonRun->season][$dungeonRun->dungeon['name']] = [];

            /*If the dungeon isnt already there*/
            if(in_array($dungeonRun->toArray(), $runs[$dungeonRun->season][$dungeonRun->dungeon['name']])) {
                array_push($runs[$dungeonRun->season][$dungeonRun->dungeon['name']], $dungeonRun->toArray());
                $character->dungeon_runs = $runs;
            }

            if(isset($character->mythics_synced_at) &&
                $character->mythics_synced_at->diffInSeconds() > config('blizzard.character_min_seconds_update')) {
                RetrieveCharacterData::dispatch($teamMember['region'], $teamMember['realm'], $teamMember['name']);
            }

            $character->save();
        }

    }


}
