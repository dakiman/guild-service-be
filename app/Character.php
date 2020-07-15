<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $guarded = [];

    protected $casts = [
        'recruitment' => 'boolean'
    ];

    public function owner()
    {
        return $this->hasOne(User::class);
    }

    public function getCharacterDataAttribute()
    {
        return json_decode($this->attributes['character_data']);
    }

}
