<?php

namespace App\Model;

class YearUsage {
    private int $year;
    private ZoneUsage $consume;
    private ZoneUsage $generate;
    /**
     * @var MonthUsage[]
     */
    private array $months;

    /**
     * @param MonthUsage[] $months
     */
    public function __construct(int $year, ZoneUsage $consume, ZoneUsage $generate, array $months) {
        $this->year = $year;
        $this->consume = $consume;
        $this->generate = $generate;
        $this->months = $months;
    }

    public function getYear(): int {
        return $this->year;
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

