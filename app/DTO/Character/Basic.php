<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Basic extends FlexibleDataTransferObject
{
    public string $gender;
    public string $faction;
    public int $race;
    public int $class;
    public int $level;
    public int $achievement_points;
    public int $average_item_level;
    public int $equipped_item_level;
    public ?CharacterGuild $guild;

}
