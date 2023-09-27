<?php

namespace App\Http\Controllers\HtwDresden\Pillnitz\V2;

use App\Http\Controllers\Controller;
use App\Models\HtwDresden\Pillnitz\Lecture;
use App\Models\HtwDresden\Pillnitz\V2\KeyNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TimetableController extends Controller
{
    protected $times = [
        1 => [
            'start' => '07:30',
            'end' => '09:00'
        ],
        2 => [
            'start' => '09:20',
            'end' => '10:50'
        ],
        3 => [
            'start' => '11:10',
            'end' => '12:40'
        ],
        4 => [
            'start' => '13:20',
            'end' => '14:50'
        ],
        5 => [
            'start' => '15:10',
            'end' => '16:40'
        ],
        6 => [
            'start' => '17:00',
            'end' => '18:30'
        ],
        7 => [
            'start' => '18:40',
            'end' => '20:10'
        ],
    ];

    /**
     * @throws \Exception
     */
    public function index(Request $request, $course) {
        header('Content-Type: text/html; charset=ISO-8859-1');
        $url = 'https://www2.htw-dresden.de/~stpill/stuplan/plan_pill_N.txt';
        $fileContent = file_get_contents($url);
        $fileRows = explode(PHP_EOL, $fileContent);

        $headingRow = $fileRows[0];

        // Remove first row (heading row)
        array_splice($fileRows, 0, 1);
        
        $modules = [];
        foreach ($fileRows as $fileRow) {
            if (empty($fileRow)) {
                continue;
            }

            $columns = explode(',', $fileRow);
            $keyNumber = $this->explodeKeyNumber($columns[0]);

            

        }
    }

    protected function explodeKeyNumber(string $key): KeyNumber|null {
        // key has to be six characters long or is invalid
        if (strlen($key) !== 6) {
            return null;
        }
        $explodedKey = str_split($key);

        $keyNumber = new KeyNumber();
        $keyNumber->setStatus((int) $explodedKey[0]);
        $keyNumber->setCourseNumber((int) ($explodedKey[1] . $explodedKey[2]));
        $keyNumber->setPositionInTimetable((int) $explodedKey[3]);
        $keyNumber->setWeekDay((int) $explodedKey[4]);
        $keyNumber->setTimeSlot((int) $explodedKey[5]);

        return $keyNumber;
    }

    public function ical(Request $request, $course) {
        
    }
}
