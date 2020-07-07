<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    protected $guarded = [];

    public function getGuildDataAttribute()
    {
        return json_decode($this->attributes['guild_data']);
    }
}
