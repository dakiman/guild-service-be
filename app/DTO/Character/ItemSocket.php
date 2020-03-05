<?php


namespace App\DTO;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class ItemSocket extends FlexibleDataTransferObject
{
    public ?int $gem;
    public string $type;
}
