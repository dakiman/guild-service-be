<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ItemSocket extends FlexibleDataTransferObject
{
    public ?int $gem;
    public string $type;
}
