<?php

namespace App\Models\HtwDresden\Pillnitz\V2;

class KeyNumber {
    /*
    Represent the status of the lecture
    1 = Eintrag ist geändert
    2 = ohne Markierung
    3 = Eintrag mit Online-Anteil
    4 = Ausstehende Änderung
    */
    protected int $status;

    /*
    Represent the course of the lecture

    in Wintersemester:
    1 = 1. Sem. AW
    2 = 1. Sem. GB
    3 = 1. Sem. UM
    4 = 1. Sem. PM-AW
    5 = 1./2. Sem. PGB
    6 = 1./2. Sem. LE
    7 = 3. Sem. AW
    8 = 3. Sem. GB
    9 = 3. Sem. UM
    10 = 3. Sem. PM-AW
    11 = 5. Sem. AW
    12 = 5. Sem. GB
    13 = 7. Sem. GB
    14 = 7. Sem. UM
    15 = Sonstige

    in Sommersemester:
    1 = 1./2. Sem. LE
    2 = 1./2. Sem. PGB
    3 = 2. Sem. PM-AW
    4 = 2. Sem. AW
    5 = 2. Sem. GB
    6 = 2. Sem. UM
    7 = 4. Sem. AW
    8 = 4. Sem. UM
    9 = 6. Sem. AW
    10 = 6. Sem. GB
    11 = 6. Sem. UM
    12 = Sonstige
    */
    protected int $courseNumber;

    /*
    Further not used, but exists
    */
    protected int $positionInTimetable;

    /*
    Represents the weekday for this lecture
    1 = Montag
    2 = Dienstag
    3 = Mittwoch
    4 = Donnerstag
    5 = Freitag
    */
    protected int $weekDay;


    /*
    Represents the used time slot when this lecture is helt

    1 = 07:30 - 09:00
    2 = 09:20 - 10:50
    3 = 11:10 - 12:40
    4 = 13:20 - 14:50
    5 = 15:10 - 16:40
    6 = 17:00 - 18:30
    7 = 18:40 - 20:10
    */
    protected int $timeSlot;

    

    /**
     * Get the value of status
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set the value of status
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of courseNumber
     */
    public function getCourseNumber(): int
    {
        return $this->courseNumber;
    }

    /**
     * Set the value of courseNumber
     */
    public function setCourseNumber(int $courseNumber): self
    {
        $this->courseNumber = $courseNumber;

        return $this;
    }

    /**
     * Get the value of positionInTimetable
     */
    public function getPositionInTimetable(): int
    {
        return $this->positionInTimetable;
    }

    /**
     * Set the value of positionInTimetable
     */
    public function setPositionInTimetable(int $positionInTimetable): self
    {
        $this->positionInTimetable = $positionInTimetable;

        return $this;
    }

    /**
     * Get the value of weekDay
     */
    public function getWeekDay(): int
    {
        return $this->weekDay;
    }

    /**
     * Set the value of weekDay
     */
    public function setWeekDay(int $weekDay): self
    {
        $this->weekDay = $weekDay;

        return $this;
    }

    /**
     * Get the value of timeSlot
     */
    public function getTimeSlot(): int
    {
        return $this->timeSlot;
    }

    /**
     * Set the value of timeSlot
     */
    public function setTimeSlot(int $timeSlot): self
    {
        $this->timeSlot = $timeSlot;

        return $this;
    }
}