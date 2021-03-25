<?php

namespace App\Model;

use DateTime;

class MonthUsage {
    private DateTime $date;
    private ZoneUsage $consume;
    private ZoneUsage $generate;
    /**
     * @var DayUsage[]
     */
    private array $days;

    /**
     * @param DayUsage[] $days
     */
    public function __construct(DateTime $date, ZoneUsage $consume, ZoneUsage $generate, array $days) {
        $this->date = $date;
        $this->consume = $consume;
        $this->generate = $generate;
        $this->days = $days;
    }

    public function getDate(): DateTime {
        return $this->date;
    }

    public function getConsume(): ZoneUsage {
        return $this->consume;
    }

    public function getGenerate(): ZoneUsage {
        return $this->generate;
    }

    /**
     * @return DayUsage[]
     */
    public function getDays(): array {
        return $this->days;
    }

}

