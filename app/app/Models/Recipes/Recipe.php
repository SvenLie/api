<?php

namespace App\Models\Recipes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $table = 'recipe';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class)->using(IngredientRecipe::class)->withPivot(['amount', 'measurement_unit', 'ingredient_section_id']);
    }

    public function ingredientSections()
    {
        return $this->belongsToMany(IngredientSection::class);
    }

    public function recipeSteps()
    {
        return $this->hasMany(RecipeStep::class);
    }
}
