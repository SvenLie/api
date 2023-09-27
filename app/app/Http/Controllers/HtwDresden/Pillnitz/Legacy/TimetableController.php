<?php

namespace App\Http\Controllers\HtwDresden\Pillnitz\Legacy;

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

                if ($cell->childNodes->count() != 6 && $cell->childNodes->count() != 7 && $cell->childNodes->count() != 14 && $cell->childNodes->count() != 13 && $cell->childNodes->count() != 27) {
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
                        "moduleNumber" => "",
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

                    // 4 lectures same time
                } elseif ($cell->childNodes->count() == 27) {
                    $lectureOne = new Lecture();
                    $lectureTwo = new Lecture();
                    $lectureThree = new Lecture();
                    $lectureFour = new Lecture();

                    $lectureOne->setModule($cell->childNodes->item(1)->childNodes->item(0)->nodeValue);
                    $lectureOne->setModuleNumber(substr($cell->childNodes->item(1)->childNodes->item(2)->nodeValue, 0, 4));
                    $lectureOne->setType($cell->childNodes->item(3)->nodeValue);
                    if ($cell->childNodes->item(1)->childNodes->count() > 3 && $cell->childNodes->item(1)->childNodes->item(3)->hasAttributes()) {
                        $lectureOne->setLink($cell->childNodes->item(1)->childNodes->item(3)->attributes->item(0)->nodeValue);
                    } else {
                        $lectureOne->setLink("");
                    }

                    $lectureTwo->setModule($cell->childNodes->item(8)->childNodes->item(0)->nodeValue);
                    $lectureTwo->setModuleNumber(substr($cell->childNodes->item(8)->childNodes->item(2)->nodeValue, 0, 4));
                    $lectureTwo->setType($cell->childNodes->item(10)->nodeValue);
                    if ($cell->childNodes->item(8)->childNodes->count() > 3 && $cell->childNodes->item(8)->childNodes->item(3)->hasAttributes()) {
                        $lectureTwo->setLink($cell->childNodes->item(8)->childNodes->item(3)->attributes->item(0)->nodeValue);
                    } else {
                        $lectureTwo->setLink("");
                    }

                    $lectureThree->setModule($cell->childNodes->item(15)->childNodes->item(0)->nodeValue);
                    $lectureThree->setModuleNumber(substr($cell->childNodes->item(15)->childNodes->item(2)->nodeValue, 0, 4));
                    $lectureThree->setType($cell->childNodes->item(17)->nodeValue);
                    if ($cell->childNodes->item(15)->childNodes->count() > 3 && $cell->childNodes->item(15)->childNodes->item(3)->hasAttributes()) {
                        $lectureThree->setLink($cell->childNodes->item(15)->childNodes->item(3)->attributes->item(0)->nodeValue);
                    } else {
                        $lectureThree->setLink("");
                    }

                    $lectureFour->setModule($cell->childNodes->item(22)->childNodes->item(0)->nodeValue);
                    $lectureFour->setModuleNumber(substr($cell->childNodes->item(22)->childNodes->item(2)->nodeValue, 0, 4));
                    $lectureFour->setType($cell->childNodes->item(24)->nodeValue);
                    if ($cell->childNodes->item(22)->childNodes->count() > 3 && $cell->childNodes->item(22)->childNodes->item(3)->hasAttributes()) {
                        $lectureFour->setLink($cell->childNodes->item(22)->childNodes->item(3)->attributes->item(0)->nodeValue);
                    } else {
                        $lectureFour->setLink("");
                    }

                    $lectureOne->setStartingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["start"] . "+" . ($j - 1) . " day"));
                    $lectureTwo->setStartingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["start"] . "+" . ($j - 1) . " day"));
                    $lectureThree->setStartingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["start"] . "+" . ($j - 1) . " day"));
                    $lectureFour->setStartingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["start"] . "+" . ($j - 1) . " day"));
                    $lectureOne->setEndingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["end"] . "+" . ($j - 1) . " day"));
                    $lectureTwo->setEndingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["end"] . "+" . ($j - 1) . " day"));
                    $lectureThree->setEndingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["end"] . "+" . ($j - 1) . " day"));
                    $lectureFour->setEndingTimestamp(strtotime($year . "W" . $week . " " . $this->times[$i]["end"] . "+" . ($j - 1) . " day"));
                    if ($lectureOne->getStartingTimestamp() == "" || $lectureOne->getEndingTimestamp() == "" || $lectureTwo->getStartingTimestamp() == "" || $lectureTwo->getEndingTimestamp() == "" || $lectureThree->getStartingTimestamp() == "" || $lectureThree->getEndingTimestamp() == "" || $lectureFour->getStartingTimestamp() == "" || $lectureFour->getEndingTimestamp() == "") {
                        return response()->json(['error' => 'Please check your week and year combination'], 500);
                    }

                    // ignore this stuff for now
                    $lectureOne->setPlace("");
                    $lectureTwo->setPlace("");
                    $lectureThree->setPlace("");
                    $lectureFour->setPlace("");
                    $lectureOne->setLecturer("");
                    $lectureTwo->setLecturer("");
                    $lectureThree->setLecturer("");
                    $lectureFour->setLecturer("");

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

                    $lectures[] = [
                        "module" => mb_detect_encoding($lectureThree->getModule()) == "UTF-8" ? utf8_decode($lectureThree->getModule()) : $lectureThree->getModule(),
                        "moduleNumber" => mb_detect_encoding($lectureThree->getModuleNumber()) == "UTF-8" ? utf8_decode($lectureThree->getModuleNumber()) : $lectureThree->getModuleNumber(),
                        "link" => $lectureThree->getLink(),
                        "type" => mb_detect_encoding($lectureThree->getType()) == "UTF-8" ? utf8_decode($lectureThree->getType()) : $lectureThree->getType(),
                        "place" => mb_detect_encoding($lectureThree->getPlace()) == "UTF-8" ? utf8_decode($lectureThree->getPlace()) : $lectureThree->getPlace(),
                        "lecturer" => mb_detect_encoding($lectureThree->getLecturer()) == "UTF-8" ? utf8_decode($lectureThree->getLecturer()) : $lectureThree->getLecturer(),
                        "startingTimestamp" => $lectureThree->getStartingTimestamp(),
                        "endingTimestamp" => $lectureThree->getEndingTimestamp(),
                    ];

                    $lectures[] = [
                        "module" => mb_detect_encoding($lectureFour->getModule()) == "UTF-8" ? utf8_decode($lectureFour->getModule()) : $lectureFour->getModule(),
                        "moduleNumber" => mb_detect_encoding($lectureFour->getModuleNumber()) == "UTF-8" ? utf8_decode($lectureFour->getModuleNumber()) : $lectureFour->getModuleNumber(),
                        "link" => $lectureFour->getLink(),
                        "type" => mb_detect_encoding($lectureFour->getType()) == "UTF-8" ? utf8_decode($lectureFour->getType()) : $lectureFour->getType(),
                        "place" => mb_detect_encoding($lectureFour->getPlace()) == "UTF-8" ? utf8_decode($lectureFour->getPlace()) : $lectureFour->getPlace(),
                        "lecturer" => mb_detect_encoding($lectureFour->getLecturer()) == "UTF-8" ? utf8_decode($lectureFour->getLecturer()) : $lectureFour->getLecturer(),
                        "startingTimestamp" => $lectureFour->getStartingTimestamp(),
                        "endingTimestamp" => $lectureFour->getEndingTimestamp(),
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
        $url = "https://www2.htw-dresden.de/~stpill/stuplan/raiplan_pill.cgi?eingabe=" . $course . "&kweingabe=" . $week;

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

        $icalObject = "BEGIN:VCALENDAR\nX-WR-TIMEZONE:Europe/Berlin\nVERSION:2.0\nMETHOD:PUBLISH\nPRODID:-//HTWDD//Pillnitz//Lectures//DE\nBEGIN:VTIMEZONE\nTZID:Europe/Berlin\nBEGIN:STANDARD\nDTSTART:19701025T030000\nRRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\nTZNAME:CET\nTZOFFSETFROM:+0200\nTZOFFSETTO:+0100\nEND:STANDARD\nBEGIN:DAYLIGHT\nDTSTART:19700329T020000\nRRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\nTZNAME:CEST\nTZOFFSETFROM:+0100\nTZOFFSETTO:+0200\nEND:DAYLIGHT\nEND:VTIMEZONE\n";

        foreach ($weeks as $week) {
            $request = new Request();
            if ($group) {
                $request->attributes->add(['group' => $group]);
            }
            $request->attributes->add(['year' => $week['year'], 'week' => $week['weekNumber']]);

            $lectures = $timetableController->index($request, $course)->getData(true);

            foreach ($lectures as $lecture) {
                $summary = str_replace(' ', ' ',$lecture['module'] . " - " . $lecture['type']);

                $icalObject .= "BEGIN:VEVENT\nDTSTART;TZID=Europe/Berlin:" . date("Ymd\THis", $lecture['startingTimestamp']) . "\nDTEND;TZID=Europe/Berlin:" . date("Ymd\THis", $lecture['endingTimestamp']) . "\nSUMMARY:" . $summary . "\nDESCRIPTION:" . $lecture['lecturer'] . "\nURL:" . $lecture['link'] ."\nUID:" . $lecture['startingTimestamp'] . "_". $lecture['moduleNumber'] . "\nSTATUS:CONFIRMED\nLOCATION:" . $lecture['place'] ."\nEND:VEVENT\n";
            }
        }

        $icalObject .= "END:VCALENDAR";
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="cal.ics"');

        //$icalObject = str_replace(' ', '', $icalObject);
        echo $icalObject;
    }
}
