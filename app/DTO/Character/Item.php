<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Item extends FlexibleDataTransferObject
{
    public int $id;
    public int $itemLevel;

    public static function fromResponse(object $data)
    {
//        dd(json_encode($data->equipped_items[0]->item->id));
        return new self(static::mapItems($data->equipped_items));
    }

    private static function mapItems($equipped_items)
    {
        foreach ($equipped_items as $equipment) {
//            return [
//                'id' => $equipment->item->id,
//
//            ];
        }
    }
}
