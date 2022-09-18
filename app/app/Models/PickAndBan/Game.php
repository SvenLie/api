<?php

namespace App\Models\PickAndBan;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'game';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ruleSet()
    {
        return $this->belongsTo(RuleSet::class);
    }

    public function gameSet()
    {
        return $this->belongsTo(GameSet::class);
    }
}
