<?php

namespace App\Model;

class ZoneUsage {
    private float $day;
    private float $night;
    private float $total;

    public function __construct(float $day = 0, float $night = 0) {
        $this->day = $day;
        $this->night = $night;

        $this->updateTotal();
    }

    public function addDayUsage(?float $value): void {
        $this->day += $value ?? 0;

        $this->updateTotal();
    }

    public function addNightUsage(?float $value): void {
        $this->night += $value ?? 0;

        $this->updateTotal();
    }

    private function updateTotal() {
        $this->total = $this->day + $this->night;
    }

    public function getDay(int $round = 2): float {
        return round($this->day, $round);
    }

    public function getNight(int $round = 2): float {
        return round($this->night, $round);
    }

    public function getTotal(int $round = 2): float {
        return round($this->total, $round);
    }

}
