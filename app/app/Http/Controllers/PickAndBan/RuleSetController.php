<?php

namespace App\Http\Controllers\PickAndBan;

use App\Http\Controllers\Controller;
use App\Models\PickAndBan\Game;
use App\Models\PickAndBan\RuleSet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RuleSetController extends Controller
{
    public function getOwnRuleSets()
    {
        /** @var User $user */
        $user = Auth::user();
        $games = RuleSet::where('user_id',$user->id)->get();
        return response()->json($games, 200);
    }

    public function createRuleSet(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'pick_amount_per_user' => 'required|integer',
            'left_over_amount' => 'required|integer',
            'choice_amount' => 'required|integer',
        ]);
        /** @var User $user */
        $user = Auth::user();

        /** @var RuleSet $ruleset */
        $ruleset = new RuleSet();
        $ruleset->name = $request['name'];
        $ruleset->pick_amount_per_user = $request['pick_amount_per_user'];
        $ruleset->left_over_amount = $request['left_over_amount'];
        $ruleset->choice_amount = $request['choice_amount'];

        $user->ruleSets()->save($ruleset);

        return response()->json(['message' => 'Successfully created rule set'], 200);
    }

    public function editRuleSet(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int',
            'name' => 'required|string',
            'pick_amount_per_user' => 'required|integer',
            'left_over_amount' => 'required|integer',
            'choice_amount' => 'required|integer',
        ]);

        /** @var RuleSet $ruleset */
        $ruleset = RuleSet::find($request['id']);

        if (!$ruleset) {
            return response()->json(['message' => 'Rule set id is invalid'], 500);
        }

        $ruleset->name = $request['name'];
        $ruleset->pick_amount_per_user = $request['pick_amount_per_user'];
        $ruleset->left_over_amount = $request['left_over_amount'];
        $ruleset->choice_amount = $request['choice_amount'];
        $ruleset->save();

        return response()->json(['message' => 'Successfully updated rule set'], 200);
    }

    public function deleteRuleSet(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int'
        ]);

        $games = Game::where('rule_set_id', $request['id'])->get();

        foreach ($games as $game) {
            Game::destroy($game->id);
        }

        $count = RuleSet::destroy($request['id']);

        return response()->json(['message' => $count], 200);
    }
}
