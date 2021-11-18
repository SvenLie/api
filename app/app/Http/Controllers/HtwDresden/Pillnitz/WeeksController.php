<?php

namespace App\Http\Controllers\HtwDresden\Pillnitz;

use App\Http\Controllers\Controller;
use App\Models\HtwDresden\Pillnitz\Week;
use Illuminate\Support\Facades\Http;

class WeeksController extends Controller
{
    public function index() {
        libxml_use_internal_errors(true);
        $html = new \DOMDocument;
        $html->loadHTML(mb_convert_encoding($this->getWeeksTable(), 'HTML-ENTITIES'));
        $xpath = new \DOMXPath($html);
        $weeks = $xpath->query("//form/table/tr");

        $weekString = htmlentities($weeks->item(0)->nodeValue);
        $weekMinimum = substr($weekString, 3, strpos($weekString, "?") - 3);
        $weekMaximum = substr($weekString, strpos($weekString, "?") + 2, 2);

        $weeks = [];
        $currentYear = $this->getCurrentYear();
        print_r($weekMinimum);
        print_r($this->getIsoWeeksInYear($currentYear));

        if ($weekMaximum < $weekMinimum) {
            // winter semester
            for ($i = (int) $weekMinimum; $i <= (int) $this->getIsoWeeksInYear($currentYear); $i++) {
                $week = new Week();
                $week->setWeekNumber($i);
                $week->setYear($currentYear);

                $weeks[] = [
                    'weekNumber' => $week->getWeekNumber(),
                    'year' => $week->getYear()
                ];
            }

            for ($i = 1; $i <= (int) $weekMaximum; $i++) {
                $week = new Week();
                $week->setWeekNumber($i);
                $week->setYear((int) $currentYear + 1);

                $weeks[] = [
                    'weekNumber' => $week->getWeekNumber(),
                    'year' => $week->getYear()
                ];
            }
        } else {
            for ($i = (int) $weekMinimum; $i <= (int) $weekMaximum; $i++) {
                $week = new Week();
                $week->setWeekNumber($i);
                $week->setYear($currentYear);

                $weeks[] = [
                    'weekNumber' => $week->getWeekNumber(),
                    'year' => $week->getYear()
                ];
            }
        }
        
        return response()->json($weeks);
    }

    protected function getWeeksTable() {
        $url = "https://www2.htw-dresden.de/~birthv/cgi-bin/pill/raiplan_pill.cgi";

        $response = Http::get($url);

        return mb_convert_encoding($response->body(),"UTF-8");
    }

    // 28th december is in iso-8601 specification always in last week of year
    protected function getIsoWeeksInYear($year): string
    {
        $dt = new \DateTime('28.12.' . $year);
        return $dt->format('W');
    }

    protected function getCurrentWeek(): string
    {
        $dt = new \DateTime();
        return $dt->format('W');
    }

    protected function getCurrentYear(): string
    {
        $dt = new \DateTime();
        return $dt->format('Y');
    }
}
