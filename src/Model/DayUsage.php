<?php

namespace App\Model;

use DateTime;

class DayUsage {
    private DateTime $date;
    private ZoneUsage $consume;
    private ZoneUsage $generate;
    /**
     * @var HourUsage[]
     */
    private array $hours;

    /**
     * @param HourUsage[] $hours
     */
    public function __construct(DateTime $date, ZoneUsage $consume, ZoneUsage $generate, array $hours) {
        $this->date = $date;
        $this->consume = $consume;
        $this->generate = $generate;
        $this->hours = $hours;
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
     * @return HourUsage[]
     */
    public function getHours(): array {
        return $this->hours;
    }

}

