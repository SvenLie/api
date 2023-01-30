<?php

namespace App\Http\Controllers\Recipes;

use App\Http\Controllers\Controller;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeStep;
use Illuminate\Support\Facades\Validator;

class RecipeStepController extends Controller
{
    public function addStepToRecipe(array $stepArray, Recipe $recipe): void
    {
        Validator::make($stepArray, [
            'description' => 'required|string',
            'stepNumber' => 'required|integer'
        ])->validate();

        $recipeStep = new RecipeStep();
        $recipeStep->description = $stepArray['description'];
        $recipeStep->step_number = $stepArray['stepNumber'];

        $recipe->recipeSteps()->save($recipeStep);
    }

    public function editStepInRecipe(array $stepArray, Recipe $recipe): void
    {
        Validator::make($stepArray, [
            'id' => 'required|integer',
            'description' => 'required|string',
            'stepNumber' => 'required|integer'
        ])->validate();

        $recipeStep = RecipeStep::where('recipe_step.id',$stepArray['id']);
        $recipeStep->description = $stepArray['description'];
        $recipeStep->step_number = $stepArray['stepNumber'];

        $recipeStep->save();
    }

    public function checkIfStepIsInRecipe(array $stepArray, Recipe $recipe): bool
    {
        Validator::make($stepArray, [
            'id' => 'required|integer',
        ])->validate();

        $step = $recipe->recipeSteps()->where('recipe_step.id',$stepArray['id'])->first();

        if ($step) {
            return true;
        }
        return false;
    }
}
