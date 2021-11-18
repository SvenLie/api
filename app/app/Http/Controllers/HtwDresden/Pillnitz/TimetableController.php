<?php

namespace App\Http\Controllers\HtwDresden\Pillnitz;

use App\Http\Controllers\Controller;
use App\Models\HtwDresden\Pillnitz\Lecture;
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

        $week = $request->get("week");
        $year = $request->get("year");
        $group = $request->get("group");

        if(!$week) {
            return response()->json(['error' => 'No week applied'], 500);
        }

        if(!$year) {
            return response()->json(['error' => 'No year applied'], 500);
        }

        libxml_use_internal_errors(true);
        $html = new \DOMDocument;
        $html->loadHTML($this->getTimetable($course, $week, $group));
        $xpath = new \DOMXPath($html);
        $tableRows = $xpath->query("//span[contains(@class,'lilli')]/following::table[1]/tr");

        $lectures = [];

        for ($i = 1; $i < $tableRows->count(); $i++) {
            $tableRow = $tableRows->item($i);

            for ($j = 1; $j < $tableRow->childNodes->count(); $j++) {
                $cell = $tableRow->childNodes->item($j);

                if ($cell->childNodes->count() != 6) {
                    continue;
                }

                $lecture = new Lecture();
                $lecture->setModule($cell->childNodes->item(1)->childNodes->item(0)->nodeValue);
                $lecture->setModuleNumber(substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue,0,4));
                if ($cell->childNodes->item(1)->childNodes->item(3)->hasAttributes()) {
                    $lecture->setLink($cell->childNodes->item(1)->childNodes->item(3)->attributes->item(0)->nodeValue);
                }
                $lecture->setType($cell->childNodes->item(3)->nodeValue);
                $lecture->setStartingTimestamp(strtotime($year . "W" . $week. " " . $this->times[$i]["start"] . "+". ($j - 1) ." day"));
                $lecture->setEndingTimestamp(strtotime($year . "W" . $week. " " . $this->times[$i]["end"] . "+". ($j - 1)  ." day"));

                $place = substr($cell->childNodes->item(5)->nodeValue,0, strpos($cell->childNodes->item(5)->nodeValue, " -"));
                $lecturer = substr($cell->childNodes->item(5)->nodeValue,strpos($cell->childNodes->item(5)->nodeValue, "- ") + 2);
                $lecture->setPlace($place);
                $lecture->setLecturer($lecturer);

                $lectures[] = [
                    "module" => mb_detect_encoding($lecture->getModule()) == "UTF-8" ? utf8_decode($lecture->getModule()) : $lecture->getModule(),
                    "moduleNumber" => mb_detect_encoding($lecture->getModuleNumber()) == "UTF-8" ? utf8_decode($lecture->getModuleNumber()) : $lecture->getModuleNumber(),
                    "link" => $lecture->getLink(),
                    "type" => mb_detect_encoding($lecture->getType()) == "UTF-8" ? utf8_decode($lecture->getType()) : $lecture->getType(),
                    "place" => mb_detect_encoding($lecture->getPlace()) == "UTF-8" ? utf8_decode($lecture->getPlace()) : $lecture->getPlace(),
                    "lecturer" => mb_detect_encoding($lecture->getLecturer()) == "UTF-8" ? utf8_decode($lecture->getLecturer()) : $lecture->getLecturer(),
                    "startingTimestamp" => $lecture->getStartingTimestamp(),
                    "endingTimestamp" => $lecture->getEndingTimestamp(),
                ];

            }
        }

        return response()->json($lectures);

    }

    protected function getTimetable($course, $week, $group = null) {
        $url = "https://www2.htw-dresden.de/~birthv/cgi-bin/pill/raiplan_pill.cgi?eingabe=" . $course . "&kweingabe=" . $week;

        if ($group) {
            $url .= "&gruppeneing=" . $group;
        }

        $response = Http::get($url);

        return mb_convert_encoding($response->body(),"UTF-8","ISO-8859-1");
    }
}
