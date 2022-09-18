<?php

namespace App\Http\Controllers\PickAndBan;

use App\Http\Controllers\Controller;
use App\Models\PickAndBan\Game;
use App\Models\PickAndBan\GameSet;
use App\Models\PickAndBan\RuleSet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GameController extends Controller
{
    public function getOwnGames()
    {
        /** @var User $user */
        $user = Auth::user();

        $games = Game::where('user_id', $user->id)->get();
        return response()->json($games, 200);
    }

    public function createGame(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'rule_set_id' => 'required|integer',
            'game_set_id' => 'required|integer',
        ]);

        /** @var GameSet $gameSet */
        $gameSet = GameSet::find($request['game_set_id']);
        /** @var RuleSet $ruleSet */
        $ruleSet = RuleSet::find($request['rule_set_id']);

        if (!$gameSet || !$ruleSet) {
            return response()->json(['message' => 'Game or rule set id is invalid'], 500);
        }
        /** @var User $user */
        $user = Auth::user();
        $joinCode = Hash::make($user->getAuthPassword() . time() . $ruleSet->id . $gameSet->id);

        $game = new Game();
        $game->name = $request['name'];
        $game->join_code = $joinCode;
        $game->finished = false;

        $gameSet->games()->save($game);
        $ruleSet->games()->save($game);
        $user->games()->save($game);

        return response()->json(['message' => 'Successfully created game', 'joinCode' => $joinCode], 200);
    }

    public function editGame(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int',
            'name' => 'required|string',
            'rule_set_id' => 'required|integer',
            'game_set_id' => 'required|integer',
        ]);

        /** @var GameSet $gameSet */
        $gameSet = GameSet::find($request['game_set_id']);
        /** @var RuleSet $ruleSet */
        $ruleSet = RuleSet::find($request['rule_set_id']);
        /** @var Game $game */
        $game = Game::find($request['id']);

        if (!$gameSet || !$ruleSet || !$game) {
            return response()->json(['message' => 'Game, game set or rule set id is invalid'], 500);
        }
        /** @var User $user */
        $user = Auth::user();

        $game->name = $request['name'];

        $gameSet->games()->save($game);
        $ruleSet->games()->save($game);
        $user->games()->save($game);
        $game->save();

        return response()->json(['message' => 'Successfully edited game', 'joinCode' => $game->join_code], 200);
    }

    public function deleteGame(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int'
        ]);

        $count = Game::destroy($request['id']);

        return response()->json(['message' => $count], 200);
    }

}
