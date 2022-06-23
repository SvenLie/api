<?php

namespace App\Http\Controllers\DateTime;

use App\Http\Controllers\Controller;
use App\Models\DateTime\Time;

class TimeController extends Controller
{
    public function getCurrentTimeInGermany()
    {
        date_default_timezone_set('Europe/Berlin');

        $timestamp = time();

        $time = new Time();
        $time->setHour(date("H",$timestamp));
        $time->setMinute(date("i",$timestamp));
        $time->setSecond(date("s",$timestamp));

        return response()->json([
            'hour' => (int) $time->getHour(),
            'minute' => (int) $time->getMinute(),
            'second' => (int) $time->getSecond()
        ]);
    }
}
