<?php

namespace App\Models\DateTime;

use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    /**
     * @var string
     */
    protected $hour;

    /**
     * @var string
     */
    protected $minute;

    /**
     * @var string
     */
    protected $second;

    /**
     * @return string
     */
    public function getHour(): string
    {
        return $this->hour;
    }

    /**
     * @param string $hour
     */
    public function setHour(string $hour): void
    {
        $this->hour = $hour;
    }

    /**
     * @return string
     */
    public function getMinute(): string
    {
        return $this->minute;
    }

    /**
     * @param string $minute
     */
    public function setMinute(string $minute): void
    {
        $this->minute = $minute;
    }

    /**
     * @return string
     */
    public function getSecond(): string
    {
        return $this->second;
    }

    /**
     * @param string $second
     */
    public function setSecond(string $second): void
    {
        $this->second = $second;
    }
}
