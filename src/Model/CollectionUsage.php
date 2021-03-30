<?php

namespace App\Model;

class CollectionUsage {
    private array $days;
    private array $months;
    private array $years;

    /**
     * CollectionUsage constructor.
     * @param DayUsage[] $days
     * @param MonthUsage[] $months
     * @param YearUsage[] $years
     */
    public function __construct(array $days = [], array $months = [], array $years = []) {
        $this->days = $days;
        $this->months = $months;
        $this->years = $years;
    }

    public function addDayUsage(DayUsage $dayUsage): void {
        $this->days[] = $dayUsage;
    }

    public function addMonthUsage(MonthUsage $monthUsage): void {
        $this->months[] = $monthUsage;
    }

    public function addYearUsage(YearUsage $yearUsage): void {
        $this->years[] = $yearUsage;
    }

    /**
     * @return DayUsage[]
     */
    public function getDays(): array {
        return $this->days;
    }

    /**
     * @return MonthUsage[]
     */
    public function getMonths(): array {
        return $this->months;
    }

    /**
     * @return YearUsage[]
     */
    public function getYears(): array {
        return $this->years;
    }
}
