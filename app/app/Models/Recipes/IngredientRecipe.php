<?php

namespace App\Models\Recipes;

use Illuminate\Database\Eloquent\Relations\Pivot;

class IngredientRecipe extends Pivot
{
    protected $table = 'ingredient_recipe';

    public function ingredientSection()
    {
        return $this->hasOne(IngredientSection::class);
    }
}
