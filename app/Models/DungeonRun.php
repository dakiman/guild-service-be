<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class DungeonRun extends Model
{
    protected $guarded = [];

    public function players()
    {
        return $this->belongsToMany(Character::class);
    }

}
