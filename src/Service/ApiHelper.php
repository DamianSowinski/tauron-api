<?php

namespace App\Service;

class ApiHelper {

    private string $regexDate;
    private string $regexMonthDate;

    public function __construct(string $regexDate, string $regexMonthDate) {
        $this->regexDate = $regexDate;
        $this->regexMonthDate = $regexMonthDate;
    }

    public function checkDate(string $date): bool {
        return preg_match($this->regexDate, $date, $matches, PREG_OFFSET_CAPTURE, 0);
    }

    public function checkMonthDate(string $monthDate): bool {
        return preg_match($this->regexMonthDate, $monthDate, $matches, PREG_OFFSET_CAPTURE, 0);
    }

}
