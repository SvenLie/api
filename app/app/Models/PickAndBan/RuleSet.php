<?php

namespace App\Models\PickAndBan;

use App\Models\User;

class RuleSet extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'rule_set';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function deleteRuleSet()
    {

    }
}
