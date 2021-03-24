<?php

namespace App\Model;

class HourUsage {
    private int $hour;
    private float $consume;
    private float $generate;

    public function __construct(int $hour, ?float $consume = 0, ?float $generate = 0) {
        $this->hour = $hour;
        $this->consume = $consume ?? 0;
        $this->generate = $generate ?? 0;
    }

    public function getHour(): int {
        return $this->hour;
    }

    public function getConsume(int $round = 3): float {
        return round($this->consume, $round);
    }

    public function getGenerate(int $round = 3): float {
        return round($this->generate, $round);
    }
}
