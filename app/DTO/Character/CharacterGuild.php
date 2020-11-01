<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class CharacterGuild extends FlexibleDataTransferObject
{
    public string $name;
    public string $realm;
    public ?string $faction;
}
