<?php


namespace App\DTO;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class EquipmentItem extends FlexibleDataTransferObject
{
    public ?int $id;
    public ?int $itemLevel;
    public ?string $name;
    public ?string $quality;

    public static function fromData(object $equipment)
    {

        return new self(collect($equipment->equipped_items)->map(function ($item) {
            $parsedItem = [
                'id' => $item->item->id,
                'name' => $item->name,
                'quality' => $item->quality->name,
                'itemLevel' => $item->level->value,
            ];

            /*TODO Reconsider implementation */
            if (!empty($item->sockets)) {
                $parsedItem['sockets'] = [];
                foreach ($item->sockets as $socket)
                    array_push(
                        $parsedItem['sockets'],
                        ['gem' => $socket->item->id ?? null, 'type' => $socket->socket_type->name]
                    );
            }

            return $parsedItem;
        })->toArray());
    }
}
