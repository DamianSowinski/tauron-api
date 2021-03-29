<?php

namespace App\Model;

class AllUsage {
    private ZoneUsage $consume;
    private ZoneUsage $generate;
    /**
     * @var YearUsage[]
     */
    private array $years;

    /**
     * @param YearUsage[] $years
     */
    public function __construct(ZoneUsage $consume, ZoneUsage $generate, array $years) {
        $this->consume = $consume;
        $this->generate = $generate;
        $this->years = $years;
    }

    public function getConsume(): ZoneUsage {
        return $this->consume;
    }

    public function getGenerate(): ZoneUsage {
        return $this->generate;
    }

    /**
     * @return YearUsage[]
     */
    public function getYears(): array {
        return $this->years;
    }
}

