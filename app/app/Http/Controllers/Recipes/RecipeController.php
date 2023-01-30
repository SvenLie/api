<?php

namespace App\Http\Controllers\Recipes;

use App\Http\Controllers\Controller;
use App\Models\Recipes\Recipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RecipeController extends Controller
{
    protected IngredientController $ingredientController;
    protected RecipeStepController $recipeStepController;

    public function __construct(IngredientController $ingredientController, RecipeStepController $recipeStepController)
    {
        $this->ingredientController = $ingredientController;
        $this->recipeStepController = $recipeStepController;
    }

    public function getOwnRecipes()
    {
        /** @var User $user */
        $user = Auth::user();

        $recipes = Recipe::where('user_id', $user->id)->get();
        return response()->json($recipes, 200);
    }

    public function getRecipe(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $recipe = Recipe::where('user_id', $user->id)->where('id', $request['id'])->with('ingredients','ingredientSections', 'recipeSteps')->first();
        return response()->json($recipe, 200);
    }

    public function createRecipe(Request $request)
    {
        $this->validate($request, [
            'summary' => 'required|string',
            'description' => 'required|string',
            'cook_time' => 'required|integer',
            'preparation_time' => 'required|integer',
            'steps' => 'required|array',
            'ingredients' => 'required|array'
        ]);

        /** @var User $user */
        $user = Auth::user();

        $recipe = new Recipe();
        $recipe->summary = $request['summary'];
        $recipe->description = $request['summary'];
        $recipe->cook_time = $request['cook_time'];
        $recipe->preparation_time = $request['preparation_time'];

        $user->recipes()->save($recipe);

        $ingredientsArray = $request['ingredients'];

        foreach ($ingredientsArray as $ingredient) {
            try {
                $this->ingredientController->addIngredientToRecipe($ingredient, $recipe);
            } catch (ValidationException $exception) {
                $recipe->delete();
                return response()->json(['message' => $exception->getMessage()], 503);
            }
        }

        $stepsArray = $request['steps'];

        foreach ($stepsArray as $step) {
            try {
                $this->recipeStepController->addStepToRecipe($step, $recipe);
            } catch (ValidationException $exception) {
                $recipe->delete();
                return response()->json(['message' => $exception->getMessage()], 503);
            }
        }

        return response()->json(['message' => 'Successfully created recipe'], 200);
    }

    public function editRecipe(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
            'summary' => 'required|string',
            'description' => 'required|string',
            'cook_time' => 'required|integer',
            'preparation_time' => 'required|integer',
            'steps' => 'required|array',
            'ingredients' => 'required|array'
        ]);

        /** @var User $user */
        $user = Auth::user();
        $recipe = Recipe::where('user_id', $user->id)->where('id', $request['id'])->with('ingredients')->first();

        $recipe->summary = $request['summary'];
        $recipe->description = $request['summary'];
        $recipe->cook_time = $request['cook_time'];
        $recipe->preparation_time = $request['preparation_time'];

        foreach ($request['ingredients'] as $ingredient) {
            try {
                if ($this->ingredientController->checkIfIngredientIsInRecipe($ingredient, $recipe)) {
                    $this->ingredientController->editIngredientInRecipe($ingredient, $recipe);
                } else {
                    $this->ingredientController->addIngredientToRecipe($ingredient, $recipe);
                }
            } catch (ValidationException $exception) {
                return response()->json(['message' => $exception->getMessage()], 503);
            }
        }

        foreach ($request['steps'] as $step) {
            try {
                if ($this->recipeStepController->checkIfStepIsInRecipe($step, $recipe)) {
                    $this->recipeStepController->editStepInRecipe($step, $recipe);
                } else {
                    $this->recipeStepController->addStepToRecipe($step, $recipe);
                }
            } catch (ValidationException $exception) {
                return response()->json(['message' => $exception->getMessage()], 503);
            }
        }

        $user->recipes()->save($recipe);
        $recipe->save();

        return response()->json(['message' => 'Successfully edited recipe'], 200);
    }

    public function deleteRecipe(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        /** @var User $user */
        $user = Auth::user();

        Recipe::where('user_id', $user->id)->where('id', $request['id'])->delete();

        return response()->json(['message' => 'Successfully deleted recipe'], 200);
    }
}
