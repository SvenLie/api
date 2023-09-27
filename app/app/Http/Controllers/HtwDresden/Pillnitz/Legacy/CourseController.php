<?php

namespace App\Http\Controllers\HtwDresden\Pillnitz\Legacy;

use App\Http\Controllers\Controller;
use App\Models\HtwDresden\Pillnitz\Legacy\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CourseController extends Controller
{
    public function index(Request $request) {
        libxml_use_internal_errors(true);
        $html = new \DOMDocument;
        $html->loadHTML($this->getCourseTable());
        $xpath = new \DOMXPath($html);
        $tableRows = $xpath->query("//tr[contains(@class,'typB')]");

        $courses = [];

        for ($i = 0; $i < $tableRows->count(); $i++) {
            $tableRow = $tableRows->item($i);

            for ($j = 0; $j < $tableRow->childNodes->count(); $j++) {
                $cell = $tableRow->childNodes->item($j);

                if($cell->childNodes->item(3)->childNodes->count() > 0 && $cell->childNodes->item(3)->childNodes->item(0)->hasAttributes()) {
                    $course = new Course();
                    $course->setId($cell->childNodes->item(3)->childNodes->item(0)->attributes->item(0)->nodeValue);
                    $course->setDescription($cell->childNodes->item(3)->childNodes->item(0)->nodeValue);

                    $courses[] = [
                        'id' => $course->getId(),
                        'description' => $course->getDescription()
                    ];
                }
            }
        }

        return response()->json($courses);
    }

    protected function getCourseTable() {
        $url = "https://www2.htw-dresden.de/~stpill/stuplan/raiplan_pill.cgi";

        $response = Http::get($url);

        return mb_convert_encoding($response->body(),"UTF-8","ISO-8859-1");
    }
}
