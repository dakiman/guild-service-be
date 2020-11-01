<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Guild extends Model
{
    protected $guarded = [];

    public function increasePopularity()
    {
        $this->num_of_searches++;
    }
}