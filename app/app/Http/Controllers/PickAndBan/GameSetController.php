<?php

namespace App\Http\Controllers\PickAndBan;

use App\Http\Controllers\Controller;
use App\Models\PickAndBan\Game;
use App\Models\PickAndBan\GameSet;
use App\Models\PickAndBan\GameSetItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameSetController extends Controller
{
    public function getOwnGameSets()
    {
        /** @var User $user */
        $user = Auth::user();
        $games = GameSet::where('user_id',$user->id)->get();
        return response()->json($games, 200);
    }

    public function createGameSet(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string'
        ]);
        /** @var User $user */
        $user = Auth::user();

        /** @var GameSet $gameSet */
        $gameSet = new GameSet();
        $gameSet->name = $request['name'];

        $user->gameSets()->save($gameSet);

        return response()->json(['message' => 'Successfully created game set'], 200);
    }

    public function editGameSet(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int',
            'name' => 'required|string'
        ]);

        /** @var GameSet $gameSet */
        $gameSet = GameSet::find($request['id']);

        if (!$gameSet) {
            return response()->json(['message' => 'Game set id is invalid'], 500);
        }

        $gameSet->name = $request['name'];
        $gameSet->save();

        return response()->json(['message' => 'Successfully updated game set'], 200);
    }

    public function deleteGameSet(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int'
        ]);

        $games = Game::where('game_set_id', $request['id'])->get();

        foreach ($games as $game) {
            Game::destroy($game->id);
        }

        $gameSetItems = GameSetItem::where('game_set_id', $request['id'])->get();

        foreach ($gameSetItems as $gameSetItem) {
            GameSetItem::destroy($gameSetItem->id);
        }

        $count = GameSet::destroy($request['id']);

        return response()->json(['message' => $count], 200);
    }
}
