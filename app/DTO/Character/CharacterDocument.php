<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class CharacterDocument extends FlexibleDataTransferObject
{
    public string $name;
    public string $realm;
    public string $region;
    public ?int $num_of_searches;
    public ?bool $recruitment;
    public BlizzardData $blizzard_data;

    public static function fromArray(array $data)
    {
        return new static($data);
    }

}
