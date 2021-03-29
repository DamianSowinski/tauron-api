<?php

namespace App\Tests\Service;

use App\Model\ProblemException;
use App\Service\ApiHelper;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ApiHelperTest extends TestCase {

    public function testCheckContentType() {
        $mockRequest = new Request();
        $apiTest = new ApiHelper($_ENV['REGEX_DATE'], $_ENV['REGEX_MONTH_DATE'], $_ENV['MONTH_DATE_FORMAT']);

        try {
            $apiTest->checkContentType($mockRequest);
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        $mockRequest->headers->set('Content-Type', 'application/json');
        try {
            $apiTest->checkContentType($mockRequest);
        } catch (ProblemException $e) {
            self::assertTrue(false, 'Content-Type == application/json should be valid');
        }

        $mockRequest->headers->set('Content-Type', 'text/html');
        try {
            $apiTest->checkContentType($mockRequest);
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }
    }

    public function testCheckQueryParameterExist() {
        $mockRequest = new Request();
        $apiTest = new ApiHelper($_ENV['REGEX_DATE'], $_ENV['REGEX_MONTH_DATE'], $_ENV['MONTH_DATE_FORMAT']);

        try {
            $apiTest->checkQueryParameterExist($mockRequest, 'par1');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        $mockRequest->query->set('par1', '100');
        try {
            $apiTest->checkQueryParameterExist($mockRequest, 'par1');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }
    }

    public function testCheckDate() {
        $apiTest = new ApiHelper($_ENV['REGEX_DATE'], $_ENV['REGEX_MONTH_DATE'], $_ENV['MONTH_DATE_FORMAT']);

        try {
            $apiTest->checkDate('20-10-2020');
        } catch (ProblemException $e) {
            self::assertTrue(false, '20-10-2020 should be valid');
        }

        try {
            $apiTest->checkDate('2020-10-20');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        try {
            $apiTest->checkDate('2020-20-10');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        try {
            $apiTest->checkDate('20-10-20');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        try {
            $apiTest->checkDate('20/10/2020');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        try {
            $apiTest->checkDate('20.10.2020');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        try {
            $apiTest->checkDate('2.1.2020');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }
    }

    public function testCheckMonthDate() {
        $apiTest = new ApiHelper($_ENV['REGEX_DATE'], $_ENV['REGEX_MONTH_DATE'], $_ENV['MONTH_DATE_FORMAT']);

        try {
            $apiTest->checkMonthDate('10-2020');
        } catch (ProblemException $e) {
            self::assertTrue(false, '10-2020 should be valid');
        }

        try {
            $apiTest->checkMonthDate('2020-10');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        try {
            $apiTest->checkMonthDate('10-20');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        try {
            $apiTest->checkMonthDate('10/2020');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        try {
            $apiTest->checkMonthDate('10.2020');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }

        try {
            $apiTest->checkMonthDate('1.2020');
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }
    }

    public function testCheckYear() {
        $apiTest = new ApiHelper($_ENV['REGEX_DATE'], $_ENV['REGEX_MONTH_DATE'], $_ENV['MONTH_DATE_FORMAT']);
        $year = new DateTime();
        $year = $year->format('Y');

        try {
            $apiTest->checkYear(1969);
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }
        try {
            $apiTest->checkYear(1970);
        } catch (ProblemException $e) {
            self::assertTrue(false, '1970 should be valid');
        }

        try {
            $apiTest->checkYear(+$year);
        } catch (ProblemException $e) {
            self::assertTrue(false, `${$year} should be valid`);
        }

        try {
            $apiTest->checkYear(+$year + 1);
        } catch (ProblemException $e) {
            self::assertTrue(true);
            self::assertEquals(400, $e->getStatusCode());
        }
    }

    public function testCheckRange() {
        $apiTest = new ApiHelper($_ENV['REGEX_DATE'], $_ENV['REGEX_MONTH_DATE'], $_ENV['MONTH_DATE_FORMAT']);

        try {
            $apiTest->checkRange('09-2020', '10-2020');
            self::assertTrue(true);
        } catch (ProblemException $e) {
            self::assertTrue(false, '09-2020 - 10-2020 should be valid');
        }

        try {
            $apiTest->checkRange('10-2020', '10-2020');
            self::assertTrue(true);
        } catch (ProblemException $e) {
            self::assertTrue(false, '10-2020 - 10-2020 should be valid');
        }

        try {
            $apiTest->checkRange('11-2020', '10-2020');
            self::assertTrue(false, 'Start date later than end date should be invalid');
        } catch (ProblemException $e) {
            self::assertTrue(true);
        }

        try {
            $apiTest->checkRange('2020-09', '2020-10');
            self::assertTrue(false, 'Wrong date format should be invalid');
        } catch (ProblemException $e) {
            self::assertTrue(true);
        }
    }

    public function testSetDatePart() {
        $apiHelper = new ApiHelper($_ENV['REGEX_DATE'], $_ENV['REGEX_MONTH_DATE'], $_ENV['MONTH_DATE_FORMAT']);
        $date = DateTime::createFromFormat('d-m-Y', '01-01-2021');

        $apiHelper->setDatePart($date, 2);
        self::assertEquals('02-01-2021', $date->format('d-m-Y'));

        $apiHelper->setDatePart($date, 3, 4);
        self::assertEquals('03-04-2021', $date->format('d-m-Y'));

        $apiHelper->setDatePart($date, 5, 6, 2019);
        self::assertEquals('05-06-2019', $date->format('d-m-Y'));
    }

    public function testMonthsDiff() {
        $apiHelper = new ApiHelper($_ENV['REGEX_DATE'], $_ENV['REGEX_MONTH_DATE'], $_ENV['MONTH_DATE_FORMAT']);

        $date1 = DateTime::createFromFormat('d-m-Y', '01-01-2021');
        $date2 = DateTime::createFromFormat('d-m-Y', '02-01-2021');
        $monthsDiff = $apiHelper->monthsDiff($date1, $date2);
        self::assertEquals(0, $monthsDiff);

        $date1 = DateTime::createFromFormat('d-m-Y', '01-01-2021');
        $date2 = DateTime::createFromFormat('d-m-Y', '01-02-2021');
        $monthsDiff = $apiHelper->monthsDiff($date1, $date2);
        self::assertEquals(1, $monthsDiff);

        $date1 = DateTime::createFromFormat('d-m-Y', '01-12-2020');
        $date2 = DateTime::createFromFormat('d-m-Y', '01-01-2021');
        $monthsDiff = $apiHelper->monthsDiff($date1, $date2);
        self::assertEquals(1, $monthsDiff);

        $date1 = DateTime::createFromFormat('d-m-Y', '01-01-2021');
        $date2 = DateTime::createFromFormat('d-m-Y', '01-01-2022');
        $monthsDiff = $apiHelper->monthsDiff($date1, $date2);
        self::assertEquals(12, $monthsDiff);

        $date1 = DateTime::createFromFormat('d-m-Y', '01-02-2021');
        $date2 = DateTime::createFromFormat('d-m-Y', '01-01-2021');
        $monthsDiff = $apiHelper->monthsDiff($date1, $date2);
        self::assertEquals(-1, $monthsDiff);

        $date1 = DateTime::createFromFormat('d-m-Y', '15-01-2021');
        $date2 = DateTime::createFromFormat('d-m-Y', '01-01-2021');
        $monthsDiff = $apiHelper->monthsDiff($date1, $date2);
        self::assertEquals(0, $monthsDiff);

        $date1 = DateTime::createFromFormat('m-Y', '01-2021');
        $date2 = DateTime::createFromFormat('m-Y', '03-2021');
        $monthsDiff = $apiHelper->monthsDiff($date1, $date2);
        self::assertEquals(2, $monthsDiff);

        $date1 = DateTime::createFromFormat('m-Y', '03-2021');
        $date2 = DateTime::createFromFormat('m-Y', '01-2021');
        $monthsDiff = $apiHelper->monthsDiff($date1, $date2);
        self::assertEquals(-2, $monthsDiff);
    }
}
