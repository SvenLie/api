<?php

namespace App\Models\HtwDresden\Pillnitz;

use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $moduleNumber;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $place;

    /**
     * @var string
     */
    protected $lecturer;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var string
     */
    protected $startingTimestamp;

    /**
     * @var string
     */
    protected $endingTimestamp;

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @param string $module
     */
    public function setModule(string $module): void
    {
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getModuleNumber(): string
    {
        return $this->moduleNumber;
    }

    /**
     * @param string $moduleNumber
     */
    public function setModuleNumber(string $moduleNumber): void
    {
        $this->moduleNumber = $moduleNumber;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getPlace(): string
    {
        return $this->place;
    }

    /**
     * @param string $place
     */
    public function setPlace(string $place): void
    {
        $this->place = $place;
    }

    /**
     * @return string
     */
    public function getLecturer(): string
    {
        return $this->lecturer;
    }

    /**
     * @param string $lecturer
     */
    public function setLecturer(string $lecturer): void
    {
        $this->lecturer = $lecturer;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getStartingTimestamp(): string
    {
        return $this->startingTimestamp;
    }

    /**
     * @param string $startingTimestamp
     */
    public function setStartingTimestamp(string $startingTimestamp): void
    {
        $this->startingTimestamp = $startingTimestamp;
    }

    /**
     * @return string
     */
    public function getEndingTimestamp(): string
    {
        return $this->endingTimestamp;
    }

    /**
     * @param string $endingTimestamp
     */
    public function setEndingTimestamp(string $endingTimestamp): void
    {
        $this->endingTimestamp = $endingTimestamp;
    }
}
