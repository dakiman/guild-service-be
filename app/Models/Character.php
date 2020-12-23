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

    public function bestMythicRuns()
    {
        return $this->belongsToMany(DungeonRun::class, null, 'players', 'best_mythic_runs')->wherePivot('best_run', true);
    }

    public function allMythicRuns()
    {
        return $this->belongsToMany(DungeonRun::class, null, 'players', 'all_mythic_runs');
    }

}
