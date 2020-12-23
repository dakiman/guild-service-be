<?php


namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model;


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

    public function dungeonRuns()
    {
        return $this->embedsMany(DungeonRun::class, 'dungeon_runs');
    }

}
