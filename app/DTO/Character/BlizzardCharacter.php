<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class BlizzardCharacter extends FlexibleDataTransferObject
{
    public int $id;
    public string $name;
    public string $gender;
    public string $faction;
    public int $race;
    public int $class;
    public string $realm;
    public int $level;
    public int $achievementPoints;
    public int $averageItemLevel;
    public int $equippedItemLevel;
    /** @var \App\DTO\Character\CharacterGuild|null */
    public $guild;
    /** @var \App\DTO\Character\CharacterMedia|null*/
    public $media;
    /** @var \App\DTO\Character\EquipmentItem[]|null  */
    public $equipment;

    public static function fromData(object $character): self
    {
        return new self([
            'id' => $character->id,
            'name' => $character->name,
            'gender' => $character->gender->name,
            'faction' => $character->faction->name,
            'race' => $character->race->id,
            'class' => $character->character_class->id,
            'realm' => $character->realm->name,
            'guild' => empty($character->guild) ? null : CharacterGuild::fromData($character->guild),
            'level' => $character->level,
            'achievementPoints' => $character->achievement_points,
            'averageItemLevel' => $character->average_item_level,
            'equippedItemLevel' => $character->equipped_item_level
        ]);
    }

}
