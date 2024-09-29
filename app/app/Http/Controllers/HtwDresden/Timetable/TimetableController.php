<?php

namespace App\Http\Controllers\HtwDresden\Timetable;

use App\Http\Controllers\Controller;
use App\Models\HtwDresden\Timetable\Lecture;
use DateMalformedPeriodStringException;
use DateMalformedStringException;
use DateTime;
use DatePeriod;
use DateInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class TimetableController extends Controller
{
    protected $weekdays = [
        1 => 'Montag',
        2 => 'Dienstag',
        3 => 'Mittwoch',
        4 => 'Donnerstag',
        5 => 'Freitag',
    ];

    public function modules(Request $request): JsonResponse
    {
        $studentNumber = $request->get("matNumber");

        if(!$studentNumber) {
            return response()->json(['error' => 'No student number (Matrikelnummer) applied'], 500);
        }

        $lectureDays = $this->lectures($request)->getData(true);
        $uniqueModules = [];

        foreach ($lectureDays as $lectures) {
            foreach ($lectures as $lecture) {
                $uniqueModules[str_replace('/', '-', $lecture['moduleNumber'])] = $lecture['module'];
            }
        }

        asort($uniqueModules);

        return response()->json($uniqueModules);
    }

    public function lectures(Request $request): JsonResponse
    {
        $studentNumber = $request->get("matNumber");
        $ignoredModules = $request->get('ignoredModules');

        $ignoredModulesArray = [];
        if ($ignoredModules) {
            $ignoredModulesArray = explode(',', $ignoredModules);
        }

        if(!$studentNumber) {
            return response()->json(['error' => 'No student number (Matrikelnummer) applied'], 500);
        }

        libxml_use_internal_errors(true);
        $html = new \DOMDocument;
        $html->loadHTML($this->getTimetableHTML($studentNumber));
        return response()->json($this->convertTimetableToLectures($html, $ignoredModulesArray));
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateMalformedPeriodStringException
     */
    public function timetable(Request $request): JsonResponse
    {
        $studentNumber = $request->get('matNumber');

        if(!$studentNumber) {
            return response()->json(['error' => 'No student number (Matrikelnummer) applied'], 500);
        }

        $lectures = $this->lectures($request)->getData(true);

        if (empty($lectures)) {
            return response()->json();
        }

        libxml_use_internal_errors(true);
        $dates = $this->getDatesFromCurrentSemester();

        $period = new DatePeriod(
            new DateTime($dates['start']),
            new DateInterval('P1D'),
            new DateTime($dates['end'])
        );

        $timetableEntries = [];

        /** @var DateTime $value */
        foreach ($period as $value) {
            $weekday = date("w", $value->getTimestamp());
            $isWeekOdd = date("W", $value->getTimestamp()) % 2;


            // weekend
            if ($weekday == 0 || $weekday == 6) {
                continue;
            }

            $germanWeekday = $this->weekdays[$weekday];

            foreach ($lectures[$germanWeekday] as $lecture) {
                if ($lecture['period'] == 'Gerade Woche' && $isWeekOdd == 1) {
                    continue;
                }

                if ($lecture['period'] == 'Ungerade Woche' && $isWeekOdd == 0) {
                    continue;
                }

                $startArray = explode(":", $lecture['start']);
                $endArray = explode(":", $lecture['end']);

                $timetableEntries[] = [
                    'moduleNumber' => $lecture['moduleNumber'],
                    'module' => $lecture['module'],
                    'place' => $lecture['place'],
                    'lecturer' => $lecture['lecturer'],
                    'type' => $lecture['type'],
                    'startTimestamp' => (new DateTime($value->format('d.m.Y')))->setTime($startArray[0], $startArray[1])->getTimestamp(),
                    'endTimestamp' => (new DateTime($value->format('d.m.Y')))->setTime($endArray[0], $endArray[1])->getTimestamp()
                ];
            }
        }

        return response()->json($timetableEntries);
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateMalformedPeriodStringException
     */
    public function timetableICAL(Request $request): void
    {
        $studentNumber = $request->get("matNumber");

        if(!$studentNumber) {
            echo 'No student number (Matrikelnummer) applied';
            exit;
        }

        $timetableEntries = $this->timetable($request)->getData(true);

        define('ICAL_FORMAT','Ymd\THis\Z');
        $icalObject = "BEGIN:VCALENDAR\nX-WR-TIMEZONE:Europe/Berlin\nVERSION:2.0\nMETHOD:PUBLISH\nPRODID:-//HTWDD///Lectures//DE\nBEGIN:VTIMEZONE\nTZID:Europe/Berlin\nBEGIN:STANDARD\nDTSTART:19701025T030000\nRRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\nTZNAME:CET\nTZOFFSETFROM:+0200\nTZOFFSETTO:+0100\nEND:STANDARD\nBEGIN:DAYLIGHT\nDTSTART:19700329T020000\nRRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\nTZNAME:CEST\nTZOFFSETFROM:+0100\nTZOFFSETTO:+0200\nEND:DAYLIGHT\nEND:VTIMEZONE\n";

        foreach ($timetableEntries as $timetableEntry) {
            $summary = sprintf('%s - %s', $timetableEntry['module'], $timetableEntry['type']);

            $icalObject .= "BEGIN:VEVENT\nDTSTART;TZID=Europe/Berlin:" . date("Ymd\THis", $timetableEntry['startTimestamp']) . "\nDTEND;TZID=Europe/Berlin:" . date("Ymd\THis", $timetableEntry['endTimestamp']) . "\nSUMMARY:" . $summary . "\nDESCRIPTION:" . $timetableEntry['lecturer'] ."\nUID:" . $timetableEntry['startTimestamp'] . "_". $timetableEntry['moduleNumber'] . "\nSTATUS:CONFIRMED\nLOCATION:" . $timetableEntry['place'] ."\nEND:VEVENT\n";
        }

        $icalObject .= "END:VCALENDAR";
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="cal.ics"');

        echo $icalObject;
    }

    public function getTimetableHTML(string $studentNumber): string
    {
        $url = "https://www.htw-dresden.de/studium/im-studium/aktuelle-stunden-und-raumplaene?tx_htwddtimetable_timetable[action]=list&tx_htwddtimetable_timetable[controller]=Timetable";

        $request = Http::asForm()
            ->post($url, [
                'tx_htwddtimetable_timetable[with-additional]' => 'true',
                'tx_htwddtimetable_timetable[mat-number]' => $studentNumber
            ]);

        return $request->body();
    }

    public function getDatesFromCurrentSemester()
    {
        $url = "https://www.htw-dresden.de/studium/im-studium/aktuelle-stunden-und-raumplaene";
        $requestBody = Http::get($url)->body();
        $html = new \DOMDocument;
        $html->loadHTML($requestBody);
        $xml = simplexml_import_dom($html);
        $title = $xml->xpath('//div[@id="tx-htwdd-timetable"]/descendant::h3/text()')[0]->__toString();

        $dates['start'] = substr($title, strpos($title, "(") + 1, 10);
        $dates['end'] = substr($title, strpos($title, ")") - 10, 10);

        return $dates;
    }

    public function convertTimetableToLectures(\DOMDocument $html, array $ignoredModules): array
    {
        $xml = simplexml_import_dom($html);
        $tableRows = $xml->xpath('//div[@id="list-compact"]/descendant::table/tr');


        $lectures = [];
        $previousLecture = null;
        /** @var SimpleXMLElement $tableRow */
        foreach ($tableRows as $tableRow) {
            $elements = $tableRow->td;
            $lecture = new Lecture([
                'moduleNumber' => substr(trim($elements[0]->__toString()), 1, strpos(trim($elements[0]->__toString()), " ") - 1),
                'module' => trim($elements[1]->__toString()),
                'type' => trim($elements[2]->__toString()),
                'weekday' => trim($elements[3]->__toString()),
                'period' => trim($elements[4]->__toString()),
                'start' => trim($elements[5]->__toString()),
                'end' => trim($elements[6]->__toString()),
                'place' => trim($elements[7]->div->__toString()),
                'lecturer' => trim($elements[9]->__toString())
            ]);

            if (in_array(str_replace('/', '-', $lecture->moduleNumber), $ignoredModules)) {
                continue;
            }

            // Gremienblockzeit
            if ($lecture->type == "Block") {
                continue;
            }

            // ignore double entries for odd and even week
            if ($previousLecture && $previousLecture->moduleNumber == $lecture->moduleNumber && $previousLecture->weekday == $lecture->weekday && $previousLecture->period == $lecture->period && $previousLecture->start == $lecture->start && $previousLecture->end == $lecture->end) {
                continue;
            }

            $previousLecture = $lecture;
            $lectures[$lecture['weekday']][] = $lecture;
        }

        return $lectures;
    }
}
