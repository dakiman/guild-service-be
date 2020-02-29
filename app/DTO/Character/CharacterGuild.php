<?php


namespace App\DTO;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class CharacterGuild extends FlexibleDataTransferObject
{
    public int $id;
    public string $name;
    public string $realm;

    public static function fromData(object $guild)
    {
        return new self([
            'id' => $guild->id,
            'name' => $guild->name,
            'realm' => $guild->realm->name,
        ]);
    }
}
