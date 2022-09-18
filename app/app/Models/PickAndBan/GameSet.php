<?php

namespace App\Models\PickAndBan;

use App\Models\User;

class GameSet extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'game_set';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function gameSetItems()
    {
        return $this->hasMany(GameSetItem::class);
    }
}
