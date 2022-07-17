<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresenceController extends Controller
{
    public function getCurrentPresenceStatus(Request $request)
    {
        $apiKeyFromEnv = env('API_KEY');
        $apiKey = $request->input('api-key');
        if (empty($apiKey) || $apiKey != $apiKeyFromEnv) {
            return response()->json(['error' => 'Invalid or empty api key'], 500);
        }

        $presentEntryId = $request->input('entry');
        if (empty($presentEntryId)) {
            return response()->json(['error' => 'Empty present entry id'], 500);
        }

        $presentEntry = DB::table('presence')->select('present')->where('id',$presentEntryId)->first();
        if (empty($presentEntry) || !is_int($presentEntry->present)) {
            return response()->json(['error' => 'Invalid present entry'], 500);
        }

        return response()->json(['present' => $presentEntry->present]);
    }

    public function setCurrentPresenceStatus(Request $request)
    {
        $apiKeyFromEnv = env('API_KEY');
        $apiKey = $request->input('api-key');

        if (empty($apiKey) || $apiKey != $apiKeyFromEnv) {
            return response()->json(['error' => 'Invalid or empty api key'], 500);
        }

        $presentEntryId = $request->input('entry');
        if (empty($presentEntryId)) {
            return response()->json(['error' => 'Empty present entry id'], 500);
        }

        $presentValue = $request->input('present');
        if (empty($presentValue) && $presentValue != 0) {
            return response()->json(['error' => 'Empty present value'], 500);
        }

        DB::table('presence')->where('id',$presentEntryId)->update(['present' => $presentValue]);

        return response()->json(['error' => '']);
    }
}
