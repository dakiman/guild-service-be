<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Talent extends FlexibleDataTransferObject
{
    public ?int $id;
    public ?int $row;
    public ?int $column;
}
