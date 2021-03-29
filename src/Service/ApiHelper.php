<?php

namespace App\Service;

use App\Model\Problem;
use App\Model\ProblemException;
use DateTime;
use Symfony\Component\HttpFoundation\Request;

class ApiHelper {

    private string $regexDate;
    private string $regexMonthDate;

    public function __construct(string $regexDate, string $regexMonthDate) {
        $this->regexDate = $regexDate;
        $this->regexMonthDate = $regexMonthDate;
    }

    public function checkContentType(Request $request): void {
        $contentType = $request->headers->get('Content-Type');

        if ('application/json' !== $contentType) {
            $problem = new Problem(400, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', 'Content-Type header must be set as application/json');
            throw new ProblemException($problem);
        }
    }

    public function checkQueryParameterExist(Request $request, string $parameter) {
        if (!$request->query->get($parameter)) {
            $problem = new Problem(400, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', sprintf('Query parameter: %s not exist', $parameter));
            throw new ProblemException($problem);
        }
    }

    public function checkDate(string $date): void {
        $isValidDate = preg_match($this->regexDate, $date, $matches, PREG_OFFSET_CAPTURE);

        if (!$isValidDate) {
            $problem = new Problem(400, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', 'Invalid date format, It must be in DD-MM-YYYY format.');
            throw new ProblemException($problem);
        }
    }

    public function checkMonthDate(string $monthDate): void {
        $isValidDate = preg_match($this->regexMonthDate, $monthDate, $matches, PREG_OFFSET_CAPTURE);

        if (!$isValidDate) {
            $problem = new Problem(400, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', 'Invalid date format, It must be in MM-YYYY format.');
            throw new ProblemException($problem);
        }
    }

    public function checkYear(int $year): void {
        $now = new DateTime('now');
        $isValidYear = $year >= 1970 && $year <= (int)$now->format('Y');

        if (!$isValidYear) {
            $problem = new Problem(400, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', 'Invalid year, It must be greater than 1970 and less or equal current year.');
            throw new ProblemException($problem);
        }
    }

    public function checkRange(string $startDate, string $endDate) {
        $isValidStartDate = preg_match($this->regexMonthDate, $startDate, $matches, PREG_OFFSET_CAPTURE);
        $isValidEndDate = preg_match($this->regexMonthDate, $endDate, $matches, PREG_OFFSET_CAPTURE);

        if (!$isValidStartDate || !$isValidEndDate) {
            $problem = new Problem(400, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', 'Invalid date format, It must be in MM-YYYY format.');
            throw new ProblemException($problem);
        }

        $startDateParts = explode('-', $startDate);
        $endDateParts = explode('-', $endDate);

        if ((int)$startDateParts[1] > (int)$endDateParts[1] ||
            ((int)$startDateParts[1] === (int)$endDateParts[1] && (int)$startDateParts[0] >= (int)$endDateParts[0])) {
            $problem = new Problem(400, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', 'Start date must be earlier than end date.');
            throw new ProblemException($problem);
        }
    }

    public function setDatePart(DateTime $date, int $day, int $month = null, int $year = null): void {
        $year = $year ?? $date->format('Y');
        $month = $month ?? $date->format('m');

        $date->setDate($year, $month, $day);
    }

}
