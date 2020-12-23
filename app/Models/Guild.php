<?php

namespace App\Models;

use App\DTO\Guild\GuildDocument;
use Jenssegers\Mongodb\Eloquent\Model;

class Guild extends Model
{
    protected $guarded = [];

    public function toDTO()
    {
        return new GuildDocument($this->toArray());
    }
}
