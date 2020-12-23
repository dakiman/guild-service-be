<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Covenant extends FlexibleDataTransferObject
{
    public int $id;
    public string $name;
    public int $renown;
}
