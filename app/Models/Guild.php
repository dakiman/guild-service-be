<?php

namespace App\Models;

use App\DTO\Guild\GuildDocument;
use Jenssegers\Mongodb\Eloquent\Model;

class Guild extends Model
{
    protected $guarded = [];

    /** Make Carbon work w/ mongo (specify which fields)*/
    protected $dates = [
        'roster_synced_at',
    ];


    public function toDTO()
    {
        return new GuildDocument($this->toArray());
    }
}
