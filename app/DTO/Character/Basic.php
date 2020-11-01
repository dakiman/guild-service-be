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

    public static function fromResponse(object $data)
    {
        return new self(static::mapResponseData($data));
    }

    private static function mapResponseData(object $data)
    {
        return [
            'gender' => $data->gender->name,
            'faction' => $data->faction->name,
            'race' => $data->race->id,
            'class' => $data->character_class->id,
            'level' => $data->level,
            'achievement_points' => $data->achievement_points,
            'average_item_level' => $data->average_item_level,
            'equipped_item_level' => $data->equipped_item_level
        ];
    }

}
