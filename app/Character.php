<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $guarded = [];

    public function owner()
    {
        return $this->hasOne(User::class);
    }

}
