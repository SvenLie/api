<?php

namespace App\Http\Controllers\PickAndBan;

use App\Http\Controllers\Controller;
use App\Models\PickAndBan\GameSet;
use App\Models\PickAndBan\GameSetItem;
use Illuminate\Http\Request;

class GameSetItemController extends Controller
{
    public function getAllGameSetItemsByGame(Request $request)
    {
        $this->validate($request, [
            'game_set_id' => 'required|integer',
        ]);
        /** @var GameSet $gameSet */
        $gameSet = GameSet::find($request['game_set_id']);

        if (!$gameSet) {
            return response()->json(['message' => 'Game set id is invalid'], 500);
        }

        $gameSetItems = GameSetItem::where('game_set_id',$gameSet->id)->get();
        return response()->json($gameSetItems, 200);
    }

    public function createGameSetItem(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'game_set_id' => 'required|integer'
        ]);
        /** @var GameSet $gameSet */
        $gameSet = GameSet::find($request['game_set_id']);

        if (!$gameSet) {
            return response()->json(['message' => 'Game set id is invalid'], 500);
        }

        /** @var GameSetItem $gameSetItem */
        $gameSetItem = new GameSetItem();
        $gameSetItem->name = $request['name'];

        $gameSet->gameSetItems()->save($gameSetItem);

        return response()->json(['message' => 'Successfully created game set item'], 200);
    }

    public function editGameSetItem(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int',
            'name' => 'required|string',
            'game_set_id' => 'required|integer'
        ]);
        /** @var GameSet $gameSet */
        $gameSet = GameSet::find($request['game_set_id']);
        /** @var GameSetItem $gameSetItem */
        $gameSetItem = GameSetItem::find($request['id']);

        if (!$gameSet || !$gameSetItem) {
            return response()->json(['message' => 'Game set id or game set item id is invalid'], 500);
        }

        $gameSetItem->name = $request['name'];

        $gameSet->gameSetItems()->save($gameSetItem);
        $gameSetItem->save();

        return response()->json(['message' => 'Successfully updated game set item'], 200);
    }

    public function deleteGameSetItem(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int'
        ]);

        $count = GameSetItem::destroy($request['id']);

        return response()->json(['message' => $count], 200);
    }
}
