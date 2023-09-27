<?php

namespace App\Models\HtwDresden\Pillnitz\Legacy;

use Illuminate\Database\Eloquent\Model;

class Week extends Model
{
    /**
     * @var string
     */
    protected $weekNumber;

    /**
     * @var string
     */
    protected $year;

    /**
     * @return string
     */
    public function getWeekNumber(): string
    {
        return $this->weekNumber;
    }

    /**
     * @param string $weekNumber
     */
    public function setWeekNumber(string $weekNumber): void
    {
        $this->weekNumber = $weekNumber;
    }

    /**
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * @param string $year
     */
    public function setYear(string $year): void
    {
        $this->year = $year;
    }
}
