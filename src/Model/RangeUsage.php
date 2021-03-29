<?php

namespace App\Model;

use DateTime;

class RangeUsage {
    private DateTime $startDate;
    private DateTime $endDate;
    private ZoneUsage $consume;
    private ZoneUsage $generate;
    /**
     * @var MonthUsage[]
     */
    private array $months;

    /**
     * @param MonthUsage[] $months
     */
    public function __construct(DateTime $startDate, DateTime $endDate, ZoneUsage $consume, ZoneUsage $generate, array $months) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->consume = $consume;
        $this->generate = $generate;
        $this->months = $months;
    }

    public function getStartDate(): DateTime {
        return $this->startDate;
    }

    public function getEndDate(): DateTime {
        return $this->endDate;
    }

    public function getConsume(): ZoneUsage {
        return $this->consume;
    }

    public function getGenerate(): ZoneUsage {
        return $this->generate;
    }

    /**
     * @return MonthUsage[]
     */
    public function getMonths(): array {
        return $this->months;
    }

}

