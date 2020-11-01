<?php


namespace App\Models;


use App\DTO\Character\CharacterDocument;
use Jenssegers\Mongodb\Eloquent\Model;


class Character extends Model
{
    protected $guarded = [];

    protected $casts = [
        'recruitment' => 'boolean'
    ];

    public function toDTO()
    {
        return new CharacterDocument($this->toArray());
    }

    public function owner()
    {
        return $this->hasOne(User::class);
    }

    public function increasePopularity()
    {
//        $ip = request()->ip();
//        $visits = cache('visits');
//        $visits = $visits ?? [];
//
//        if(!in_array([$ip => $this->id], $visits)) {
//            $this->num_of_searches += 1;
//            array_push($visits, [$ip => $this->id]);
//            cache(['visits' => $visits], 60);
//        }
        $this->num_of_searches++;
    }
}
