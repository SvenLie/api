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

                if ($cell->childNodes->count() != 6 && $cell->childNodes->count() != 7 && $cell->childNodes->count() != 14 && $cell->childNodes->count() != 13) {
                    continue;
                }

                // zwei lectures gleichzeitig
                if ($cell->childNodes->count() == 14 || $cell->childNodes->count() == 13) {
                    $lectureOne = new Lecture();
                    $lectureTwo = new Lecture();

                    $lectureOne->setModule($cell->childNodes->item(1)->childNodes->item(0)->nodeValue);

                    if($cell->childNodes->count() != 13) {
                        if(!empty($cell->childNodes->item(8)->childNodes->item(0)->nodeValue)) {
                            $lectureTwo->setModule($cell->childNodes->item(8)->childNodes->item(0)->nodeValue);
                            $lectureTwo->setModuleNumber(substr($cell->childNodes->item(8)->childNodes->item(2)->nodeValue, 0, 4));
                            $lectureTwo->setType($cell->childNodes->item(10)->nodeValue);
                            if ($cell->childNodes->item(8)->childNodes->count() > 3 && $cell->childNodes->item(8)->childNodes->item(3)->hasAttributes()) {
                                $lectureTwo->setLink($cell->childNodes->item(8)->childNodes->item(3)->attributes->item(0)->nodeValue);
                            } else {
                                $lectureTwo->setLink("");
                            }
                        } else {
                            $lectureTwo->setModule($cell->childNodes->item(9)->childNodes->item(0)->nodeValue);
                            $lectureTwo->setModuleNumber(substr($cell->childNodes->item(9)->childNodes->item(2)->nodeValue, 0, 4));
                            $lectureTwo->setType($cell->childNodes->item(11)->nodeValue);
                            if ($cell->childNodes->item(9)->childNodes->count() > 3 && $cell->childNodes->item(9)->childNodes->item(3)->hasAttributes()) {
                                $lectureTwo->setLink($cell->childNodes->item(9)->childNodes->item(3)->attributes->item(0)->nodeValue);
                            } else {
                                $lectureTwo->setLink("");
                            }
                        }

                    } else {
                        if(!empty($cell->childNodes->item(9)->childNodes->item(0)->nodeValue)) {
                            $lectureTwo->setModule($cell->childNodes->item(9)->childNodes->item(0)->nodeValue);
                            $lectureTwo->setModuleNumber(substr($cell->childNodes->item(9)->childNodes->item(2)->nodeValue, 0, 4));
                            $lectureTwo->setType($cell->childNodes->item(11)->nodeValue);
                            if ($cell->childNodes->item(9)->childNodes->count() > 3 && $cell->childNodes->item(9)->childNodes->item(3)->hasAttributes()) {
                                $lectureTwo->setLink($cell->childNodes->item(9)->childNodes->item(3)->attributes->item(0)->nodeValue);
                            } else {
                                $lectureTwo->setLink("");
                            }
                        } else {
                            $lectureTwo->setModule($cell->childNodes->item(8)->childNodes->item(0)->nodeValue);
                            $lectureTwo->setModuleNumber(substr($cell->childNodes->item(8)->childNodes->item(2)->nodeValue, 0, 4));
                            $lectureTwo->setType($cell->childNodes->item(10)->nodeValue);
                            if ($cell->childNodes->item(8)->childNodes->count() > 3 && $cell->childNodes->item(8)->childNodes->item(3)->hasAttributes()) {
                                $lectureTwo->setLink($cell->childNodes->item(8)->childNodes->item(3)->attributes->item(0)->nodeValue);
                            } else {
                                $lectureTwo->setLink("");
                            }
                        }

                    }

                    $lectureOne->setModuleNumber(substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue, 0, 4));
                    $lectureOne->setType($cell->childNodes->item(3)->nodeValue);

                    if ($cell->childNodes->item(1)->childNodes->count() > 3 && $cell->childNodes->item(1)->childNodes->item(3)->hasAttributes()) {
                        $lectureOne->setLink($cell->childNodes->item(1)->childNodes->item(3)->attributes->item(0)->nodeValue);
                    } else {
                        $lectureOne->setLink("");
                    }

                    $lectureOne->setStartingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["start"] . "+" . ($j - 1) . " day"));
                    $lectureTwo->setStartingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["start"] . "+" . ($j - 1) . " day"));
                    $lectureOne->setEndingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["end"] . "+" . ($j - 1) . " day"));
                    $lectureTwo->setEndingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["end"] . "+" . ($j - 1) . " day"));
                    if ($lectureOne->getStartingTimestamp() == "" || $lectureOne->getEndingTimestamp() == "" || $lectureTwo->getStartingTimestamp() == "" || $lectureTwo->getEndingTimestamp() == "") {
                        return response()->json(['error' => 'Please check your week and year combination'], 500);
                    }

                    // ignore this stuff for now
                    $lectureOne->setPlace("");
                    $lectureTwo->setPlace("");
                    $lectureOne->setLecturer("");
                    $lectureTwo->setLecturer("");

                    $lectures[] = [
                        "module" => mb_detect_encoding($lectureOne->getModule()) == "UTF-8" ? utf8_decode($lectureOne->getModule()) : $lectureOne->getModule(),
                        "moduleNumber" => mb_detect_encoding($lectureOne->getModuleNumber()) == "UTF-8" ? utf8_decode($lectureOne->getModuleNumber()) : $lectureOne->getModuleNumber(),
                        "link" => $lectureOne->getLink(),
                        "type" => mb_detect_encoding($lectureOne->getType()) == "UTF-8" ? utf8_decode($lectureOne->getType()) : $lectureOne->getType(),
                        "place" => mb_detect_encoding($lectureOne->getPlace()) == "UTF-8" ? utf8_decode($lectureOne->getPlace()) : $lectureOne->getPlace(),
                        "lecturer" => mb_detect_encoding($lectureOne->getLecturer()) == "UTF-8" ? utf8_decode($lectureOne->getLecturer()) : $lectureOne->getLecturer(),
                        "startingTimestamp" => $lectureOne->getStartingTimestamp(),
                        "endingTimestamp" => $lectureOne->getEndingTimestamp(),
                    ];

                    $lectures[] = [
                        "module" => mb_detect_encoding($lectureTwo->getModule()) == "UTF-8" ? utf8_decode($lectureTwo->getModule()) : $lectureTwo->getModule(),
                        "moduleNumber" => mb_detect_encoding($lectureTwo->getModuleNumber()) == "UTF-8" ? utf8_decode($lectureTwo->getModuleNumber()) : $lectureTwo->getModuleNumber(),
                        "link" => $lectureTwo->getLink(),
                        "type" => mb_detect_encoding($lectureTwo->getType()) == "UTF-8" ? utf8_decode($lectureTwo->getType()) : $lectureTwo->getType(),
                        "place" => mb_detect_encoding($lectureTwo->getPlace()) == "UTF-8" ? utf8_decode($lectureTwo->getPlace()) : $lectureTwo->getPlace(),
                        "lecturer" => mb_detect_encoding($lectureTwo->getLecturer()) == "UTF-8" ? utf8_decode($lectureTwo->getLecturer()) : $lectureTwo->getLecturer(),
                        "startingTimestamp" => $lectureTwo->getStartingTimestamp(),
                        "endingTimestamp" => $lectureTwo->getEndingTimestamp(),
                    ];

                } else {
                    $lecture = new Lecture();
                    if($cell->childNodes->count() == 7) {

                        if (preg_match("/[A-Z]{1}[0-9]{3}/",substr($cell->childNodes->item(3)->nodeValue, 0, 4))) {
                            $lecture->setModule($cell->childNodes->item(1)->childNodes->item(0)->nodeValue . ' ' . $cell->childNodes->item(1)->childNodes->item(2)->nodeValue);
                            $lecture->setModuleNumber(substr($cell->childNodes->item(3)->nodeValue, 0, 4));
                            $lecture->setType(substr($cell->childNodes->item(3)->nodeValue, strpos($cell->childNodes->item(3)->nodeValue," -") + 3, strrpos($cell->childNodes->item(3)->nodeValue,"-")));
                        } else {
                            $lecture->setType($cell->childNodes->item(3)->nodeValue);
                            $lecture->setModule($cell->childNodes->item(1)->childNodes->item(0)->nodeValue);
                            if (preg_match("/[A-Z]{1}[0-9]{3}/",substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue, 0, 4))) {
                                $lecture->setModuleNumber(substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue, 0, 4));
                            } else {
                                $lecture->setModule($cell->childNodes->item(1)->childNodes->item(0)->nodeValue . " " . substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue, 0,strlen($cell->childNodes->item(1)->childNodes->item(2)->nodeValue)-7));
                                $lecture->setModuleNumber(substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue, -4));
                            }

                        }
                    } else {
                        $lecture->setType($cell->childNodes->item(3)->nodeValue);
                        if (preg_match("/[A-Z]{1}[0-9]{3}/",substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue, 0, 4))) {
                            $lecture->setModuleNumber(substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue, 0, 4));
                            $lecture->setModule($cell->childNodes->item(1)->childNodes->item(0)->nodeValue);
                        } else {
                            $lecture->setModuleNumber(substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue,strrpos($cell->childNodes->item(1)->childNodes->item(2)->nodeValue,"-") + 2, 4));
                            $lecture->setModule($cell->childNodes->item(1)->childNodes->item(0)->nodeValue . " " . substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue,0,strrpos($cell->childNodes->item(1)->childNodes->item(2)->nodeValue," -")));
                        }

                    }


                    if ($cell->childNodes->item(1)->childNodes->count() > 3 && $cell->childNodes->item(1)->childNodes->item(3)->hasAttributes()) {
                        $lecture->setLink($cell->childNodes->item(1)->childNodes->item(3)->attributes->item(0)->nodeValue);
                    } else {
                        $lecture->setLink("");
                    }
                    $lecture->setStartingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["start"] . "+" . ($j - 1) . " day"));
                    $lecture->setEndingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["end"] . "+" . ($j - 1) . " day"));

                    if ($lecture->getStartingTimestamp() == "" || $lecture->getEndingTimestamp() == "") {
                        return response()->json(['error' => 'Please check your week and year combination'], 500);
                    }

                    if($cell->childNodes->count() == 7) {
                        $place = $cell->childNodes->item(5)->nodeValue . substr($cell->childNodes->item(6)->nodeValue, 0, strpos($cell->childNodes->item(6)->nodeValue, " -"));
                        $lecturer = substr($cell->childNodes->item(6)->nodeValue, strrpos($cell->childNodes->item(6)->nodeValue, "- ") + 2);
                    } else {
                        $place = substr($cell->childNodes->item(5)->nodeValue, 0, strpos($cell->childNodes->item(5)->nodeValue, " -"));
                        $lecturer = substr($cell->childNodes->item(5)->nodeValue, strrpos($cell->childNodes->item(5)->nodeValue, "- ") + 2);
                    }
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

    public function ical(Request $request, $course) {
        $group = $request->get("group");

        define('ICAL_FORMAT','Ymd\THis\Z');
        $weeksController = new WeeksController();
        $timetableController = new TimetableController();
        $weeks = $weeksController->index()->getData(true);

        $icalObject = "BEGIN:VCALENDAR\nVERSION:2.0\nMETHOD:PUBLISH\nPRODID:-//HTWDD//Pillnitz//Lectures//DE\n";

        foreach ($weeks as $week) {
            $request = new Request();
            if ($group) {
                $request->attributes->add(['group' => $group]);
            }
            $request->attributes->add(['year' => $week['year'], 'week' => $week['weekNumber']]);

            $lectures = $timetableController->index($request, $course)->getData(true);

            foreach ($lectures as $lecture) {
                $summary = str_replace(' ', ' ',$lecture['module'] . " - " . $lecture['type']);

                $icalObject .= "BEGIN:VEVENT\nDTSTART;TZID=Europe/Berlin:" . date(ICAL_FORMAT, $lecture['startingTimestamp']) . "\nDTEND;TZID=Europe/Berlin:" . date(ICAL_FORMAT, $lecture['endingTimestamp']) . "\nSUMMARY:" . $summary . "\nDESCRIPTION:" . $lecture['lecturer'] . "\nURL:" . $lecture['link'] ."\nUID:" . $lecture['startingTimestamp'] . "_". $lecture['moduleNumber'] . "\nSTATUS:CONFIRMED\nLOCATION:" . $lecture['place'] ."\nEND:VEVENT\n";
            }
        }

        $icalObject .= "END:VCALENDAR";
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="cal.ics"');

        //$icalObject = str_replace(' ', '', $icalObject);
        echo $icalObject;
    }
}
