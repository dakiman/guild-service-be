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

    public function setGuildDataAttribute($value)
    {
        $this->attributes['guild_data'] = json_encode($value);
    }

    public function increasePopularity()
    {
        $this->num_of_searches++;
    }
}
