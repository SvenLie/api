<?php

namespace App\Models\PickAndBan;

class GameSetItem extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'game_set_item';

    public function gameSet()
    {
        return $this->belongsTo(GameSet::class);
    }
}
