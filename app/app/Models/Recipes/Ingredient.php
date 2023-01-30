<?php

namespace App\Models\Recipes;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $table = 'ingredient';

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class)->using(IngredientRecipe::class);
    }
}
