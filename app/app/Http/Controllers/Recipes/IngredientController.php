<?php

namespace App\Http\Controllers\Recipes;

use App\Http\Controllers\Controller;
use App\Models\Recipes\Ingredient;
use App\Models\Recipes\IngredientSection;
use App\Models\Recipes\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class IngredientController extends Controller
{
    public function addIngredientToRecipe(array $ingredientArray, Recipe $recipe): void
    {
        Validator::make($ingredientArray, [
            'identifier' => 'required|string',
            'title' => 'required|string',
            'amount' => 'required|numeric',
            'measurementUnit' => 'required|string',
            'section.identifier' => 'required|string',
            'section.title' => 'string'
        ])->validate();

        /** @var User $user */
        $user = Auth::user();

        $existingIngredientSection = IngredientSection::where('identifier', $ingredientArray['section']['identifier'])->where('user_id',$user->id)->first();
        $existingIngredient = Ingredient::where('identifier', $ingredientArray['identifier'])->where('user_id',$user->id)->first();

        if ($existingIngredientSection === null) {
            $ingredientSection = new IngredientSection();
            $ingredientSection->identifier = $ingredientArray['section']['identifier'];
            $ingredientSection->title = $ingredientArray['section']['title'];
            $user->ingredientSections()->save($ingredientSection);
            $existingIngredientSection = $ingredientSection;
        }

        if ($existingIngredient === null) {
            $ingredient = new Ingredient();
            $ingredient->identifier = $ingredientArray['identifier'];
            $ingredient->title = $ingredientArray['title'];
            $user->ingredients()->save($ingredient);
            $existingIngredient = $ingredient;
        }

        $recipe->ingredients()->attach($existingIngredient['id'],['amount' => $ingredientArray['amount'], 'measurement_unit' => $ingredientArray['measurementUnit'], 'ingredient_section_id' => $existingIngredientSection['id']]);
        if (!$recipe->ingredientSections()->where('ingredient_section.id',$existingIngredientSection['id'])->first()) {
            $recipe->ingredientSections()->attach($existingIngredientSection['id']);
        }
    }

    public function editIngredientInRecipe(array $ingredientArray, Recipe $recipe): void
    {
        Validator::make($ingredientArray, [
            'id' => 'required|integer',
            'identifier' => 'required|string',
            'title' => 'required|string',
            'amount' => 'required|numeric',
            'measurementUnit' => 'required|string',
            'section.identifier' => 'required|string',
            'section.title' => 'string'
        ])->validate();

        /** @var User $user */
        $user = Auth::user();

        $existingIngredient = Ingredient::where('ingredient.id', $ingredientArray['id'])->where('user_id',$user->id)->first();
        $existingIngredient->title = $ingredientArray['title'];
        $existingIngredient->save();

        $existingIngredientSection = IngredientSection::where('identifier', $ingredientArray['section']['identifier'])->where('user_id',$user->id)->first();
        if ($existingIngredientSection === null) {
            $ingredientSection = new IngredientSection();
            $ingredientSection->identifier = $ingredientArray['section']['identifier'];
            $ingredientSection->title = $ingredientArray['section']['title'];
            $user->ingredientSections()->save($ingredientSection);
            $existingIngredientSection = $ingredientSection;
        }

        if (!$recipe->ingredientSections()->where('ingredient_section.id',$existingIngredientSection['id'])->first()) {
            $recipe->ingredientSections()->attach($existingIngredientSection['id']);
        }

        $recipe->ingredients()->updateExistingPivot($existingIngredient['id'],['amount' => $ingredientArray['amount'], 'measurement_unit' => $ingredientArray['measurementUnit'], 'ingredient_section_id' => $existingIngredientSection['id']]);
    }

    public function checkIfIngredientIsInRecipe(array $ingredientArray, Recipe $recipe): bool
    {
        Validator::make($ingredientArray, [
            'identifier' => 'required|string',
            'title' => 'required|string',
            'amount' => 'required|numeric',
            'measurementUnit' => 'required|string',
        ])->validate();

        $ingredient = $recipe->ingredients()->where('identifier',$ingredientArray['identifier'])->first();

        if ($ingredient) {
            return true;
        }
        return false;
    }
}
