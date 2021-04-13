<?php

namespace App\Service;

use App\Model\AllUsage;
use App\Model\CollectionUsage;
use App\Model\DayUsage;
use App\Model\HourUsage;
use App\Model\MonthUsage;
use App\Model\RangeUsage;
use App\Model\YearUsage;
use App\Model\ZoneUsage;
use DateTime;

class MockTauronService {
    private ApiHelper $apiHelper;
    private string $dateFormat;
    private string $monthDateFormat;

    public function __construct(ApiHelper $apiHelper, string $dateFormat, string $monthDateFormat) {
        $this->apiHelper = $apiHelper;
        $this->dateFormat = $dateFormat;
        $this->monthDateFormat = $monthDateFormat;
    }

    public function getDayUsage(DateTime $date, bool $includeHours = true): DayUsage {
        $consume = new ZoneUsage(rand(100, 1200) / 100, rand(100, 1200) / 100);
        $generate = new ZoneUsage(rand(100, 1200) / 100, rand(100, 1200) / 100);
        $hours = [];

        if ($includeHours) {
            for ($i = 1; $i <= 24; $i++) {
                $hours[] = new HourUsage($i, rand(0, 400) / 100, rand(0, 400) / 100);
            }
        }

        return new DayUsage($date, $consume, $generate, $hours);
    }

    public function getMonthUsage(DateTime $monthDate, bool $includeDays = true): MonthUsage {
        $consumeMonth = new ZoneUsage(rand(4000, 20000) / 100, rand(4000, 15000) / 100);
        $generateMonth = new ZoneUsage(rand(4000, 20000) / 100, rand(4000, 15000) / 100);
        $days = [];

        if ($includeDays) {
            for ($i = 1; $i <= $this->daysInMonth($monthDate); $i++) {
                $date = clone $monthDate;
                $this->apiHelper->setDatePart($date, $i);

                $consume = new ZoneUsage(rand(100, 1200) / 100, rand(100, 1200) / 100);
                $generate = new ZoneUsage(rand(100, 1200) / 100, rand(100, 1200) / 100);

                $days[] = new DayUsage($date, $consume, $generate, []);
            }
        }

        return new MonthUsage($monthDate, $consumeMonth, $generateMonth, $days);
    }

    public function getYearUsage(int $year, bool $includeMonths = true): YearUsage {
        $consumeYear = new ZoneUsage(rand(40000, 60000) / 100, rand(20000, 50000) / 100);
        $generateYear = new ZoneUsage(rand(50000, 80000) / 100, rand(20000, 70000) / 100);
        $months = [];

        if ($includeMonths) {
            for ($i = 1; $i <= 12; $i++) {
                $date = new DateTime();
                $date->setDate($year, $i, 1);
                $consume = new ZoneUsage(rand(4000, 20000) / 100, rand(4000, 15000) / 100);
                $generate = new ZoneUsage(rand(4000, 20000) / 100, rand(4000, 15000) / 100);

                $months[] = new MonthUsage($date, $consume, $generate, []);
            }
        }

        return new YearUsage($year, $consumeYear, $generateYear, $months);
    }

    public function getRangeUsage(DateTime $startDate, DateTime $endDate): RangeUsage {
        $currentDate = clone $startDate;
        $this->apiHelper->setDatePart($currentDate, 1);

        $consume = new ZoneUsage();
        $generate = new ZoneUsage();
        $months = [];

        while ($currentDate <= $endDate) {
            $monthData = $this->getMonthUsage(clone $currentDate, false);

            array_push($months, $monthData);

            $consume->addDayUsage($monthData->getConsume()->getDay());
            $consume->addNightUsage($monthData->getConsume()->getNight());
            $generate->addDayUsage($monthData->getGenerate()->getDay());
            $generate->addNightUsage($monthData->getGenerate()->getNight());

            $currentDate->modify('next month');
        }

        return new RangeUsage($startDate, $endDate, $consume, $generate, $months);
    }

    public function getAllUsage(): AllUsage {
        $date = new DateTime('');
        $year = $date->format('Y');

        $consume = new ZoneUsage();
        $generate = new ZoneUsage();
        $years = [];

        for ($i = 1; $i <= 3; $i++) {
            $yearData = $this->getYearUsage($year, false);

            array_push($years, $yearData);

            $consume->addDayUsage($yearData->getConsume()->getDay());
            $consume->addNightUsage($yearData->getConsume()->getNight());
            $generate->addDayUsage($yearData->getGenerate()->getDay());
            $generate->addNightUsage($yearData->getGenerate()->getNight());
            $year--;
        }

        return new AllUsage($consume, $generate, array_reverse($years));
    }

    public function getCollection(array $days, array $months, array $years): CollectionUsage {
        $collection = new CollectionUsage();

        foreach ($days as $day) {
            $date = DateTime::createFromFormat($this->dateFormat, $day);
            $collection->addDayUsage($this->getDayUsage($date));
        }

        foreach ($months as $month) {
            $date = DateTime::createFromFormat($this->monthDateFormat, $month);
            $collection->addMonthUsage($this->getMonthUsage($date));
        }

        foreach ($years as $year) {
            $collection->addYearUsage($this->getYearUsage($year));
        }

        return $collection;
    }

    private function daysInMonth(DateTime $date): int {
        $month = $date->format('m');
        $year = $date->format('Y');
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }

}
