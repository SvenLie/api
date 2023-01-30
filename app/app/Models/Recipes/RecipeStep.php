<?php

namespace App\Models\Recipes;

use Illuminate\Database\Eloquent\Model;

class RecipeStep extends Model
{
    protected $table = 'recipe_step';

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
