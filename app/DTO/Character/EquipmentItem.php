<?php


namespace App\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class EquipmentItem extends FlexibleDataTransferObject
{
    public ?int $id;
    public ?int $itemLevel;
    public ?string $name;
    public ?string $quality;
    /** @var \App\DTO\ItemSocket[]|null */
    public $sockets;

    public static function fromData(object $item): self
    {
        $parsedItem = [
            'id' => $item->item->id,
            'name' => $item->name,
            'quality' => $item->quality->name,
            'itemLevel' => $item->level->value,
        ];

        /*TODO Reconsider implementation */
        if (!empty($item->sockets)) {
            $parsedItem['sockets'] = [];

            foreach ($item->sockets as $socket) {
                $socket = new ItemSocket([
                    'gem' => $socket->item->id ?? null,
                    'type' => $socket->socket_type->name
                ]);

                array_push(
                    $parsedItem['sockets'],
                    $socket
                );
            }
        }

        return new self($parsedItem);
    }

}
