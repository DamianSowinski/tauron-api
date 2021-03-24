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

    public function checkContentType(Request $request) {
         $contentType = $request->headers->get('Content-Type');

        if ('application/json' !== $contentType) {
            $problem = new Problem(400, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', 'Content-Type header must be set as application/json');
            throw new ProblemException($problem);
        }
    }

    public function checkDate(string $date): void {
        $isValidDate = preg_match($this->regexDate, $date, $matches, PREG_OFFSET_CAPTURE, 0);

        if (!$isValidDate) {
            $problem = new Problem(400, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', 'Invalid date format, It must be in DD-MM-YYYY format.');
            throw new ProblemException($problem);
        }
    }

    public function checkMonthDate(string $monthDate): void {
        $isValidDate = preg_match($this->regexMonthDate, $monthDate, $matches, PREG_OFFSET_CAPTURE, 0);

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

    public function readHeaders(Request $request) {
        $sessionId = $request->headers->get('Authorization');
        $pointId = $request->headers->get('PointId');

        if (!$sessionId || !$pointId) {
            $problem = new Problem(400, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', 'Missing Session id or Point Id in header');
            throw new ProblemException($problem);
        }

//        $this->sessionId = $sessionId;
//        $this->pointId = $pointId;

    }
}
