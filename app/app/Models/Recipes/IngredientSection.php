<?php

namespace App\Models\Recipes;

use Illuminate\Database\Eloquent\Model;

class IngredientSection extends Model
{
    protected $table = 'ingredient_section';

    public function ingredientRecipes()
    {
        return $this->hasMany(IngredientRecipe::class);
    }

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class);
    }
}
