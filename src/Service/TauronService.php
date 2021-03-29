<?php

namespace App\Service;

use App\Model\DayUsage;
use App\Model\HourUsage;
use App\Model\MonthUsage;
use App\Model\Problem;
use App\Model\ProblemException;
use App\Model\RangeUsage;
use App\Model\User;
use App\Model\YearUsage;
use App\Model\ZoneUsage;
use DateTime;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TauronService {
    private const SERVICE_URL = 'https://elicznik.tauron-dystrybucja.pl';
    private const LOGIN_URL = 'https://logowanie.tauron-dystrybucja.pl/login?service=https://elicznik.tauron-dystrybucja.pl';
    private const CHARTS_URL = 'https://elicznik.tauron-dystrybucja.pl/index/charts';

    private HttpClientInterface $client;
    private ApiHelper $apiHelper;

    public function __construct(HttpClientInterface $client, ApiHelper $apiHelper) {
        $this->client = $client;
        $this->apiHelper = $apiHelper;
    }

    public function login(User $user): void {
        $sessionId = null;
        $response = null;

        try {
            $response = $this->client->request('POST', self::LOGIN_URL, [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'body' => [
                    'username' => $user->getUsername(),
                    'password' => $user->getPassword(),
                    'service' => self::SERVICE_URL
                ],
            ]);
        } catch (TransportExceptionInterface $e) {
            $this->loginError();
        }

        $sessionId = $this->getSessionId($response);

        if (null === $sessionId) {
            $this->loginError();
        }

        $user->setSessionId($sessionId);
    }

    public function getDayUsage(DateTime $date, User $user): DayUsage {
        $properties = [
            'dane[chartDay]' => $date->format('d.m.Y'),
            'dane[paramType]' => 'day',
        ];

        $data = $this->fetchData($properties, $user);
        $consume = new ZoneUsage();
        $generate = new ZoneUsage();
        $hours = [];

        if (property_exists($data, 'dane')) {
            $consumesData = property_exists($data->dane, 'chart') ? $data->dane->chart : null;
            $generatesData = property_exists($data->dane, 'OZE') ? $data->dane->OZE : null;

            for ($hour = 1; $hour <= 24; $hour++) {
                $hourConsume = $this->fetchHourUsage($consumesData, $hour, $consume);
                $hourGenerate = $this->fetchHourUsage($generatesData, $hour, $generate);

                $hours[] = new HourUsage($hour, $hourConsume, $hourGenerate);
            }
        }

        return new DayUsage($date, $consume, $generate, $hours);
    }

    public function getMonthUsage(DateTime $monthDate, User $user, bool $includeDays = true): MonthUsage {
        $properties = [
            'dane[chartMonth]' => $monthDate->format('m'),
            'dane[chartYear]' => $monthDate->format('Y'),
            'dane[paramType]' => 'month',
        ];

        $data = $this->fetchData($properties, $user);
        $consume = new ZoneUsage();
        $generate = new ZoneUsage();
        $days = [];

        if (property_exists($data, 'dane')) {
            $consumesData = property_exists($data->dane, 'chart') ? $data->dane->chart : null;
            $generatesData = property_exists($data->dane, 'OZE') ? $data->dane->OZE : null;

            for ($index = 0; $index < count($consumesData); $index++) {
                $dayConsume = new ZoneUsage();
                $dayGenerate = new ZoneUsage();

                $dayConsume->setTotal($this->fetchDayUsage($consumesData, $index, $consume));
                $dayGenerate->setTotal($this->fetchDayUsage($generatesData, $index, $generate));

                if ($includeDays) {
                    $date = clone $monthDate;
                    $this->apiHelper->setDatePart($date, $index + 1);
                    $days[] = new DayUsage($date, $dayConsume, $dayGenerate, []);
                }
            }
        }

        return new MonthUsage($monthDate, $consume, $generate, $days);
    }

    public function getYearUsage(int $year, User $user, bool $includeMonths = true): YearUsage {
        $properties = [
            'dane[chartYear]' => $year,
            'dane[paramType]' => 'year',
        ];

        $data = $this->fetchData($properties, $user);
        $consume = new ZoneUsage();
        $generate = new ZoneUsage();
        $months = [];

        if (property_exists($data, 'dane')) {
            $consumesData = property_exists($data->dane, 'chart') ? $data->dane->chart : null;
            $generatesData = property_exists($data->dane, 'OZE') ? $data->dane->OZE : null;

            for ($index = 0; $index < count($consumesData); $index++) {
                $dayConsume = new ZoneUsage();
                $dayGenerate = new ZoneUsage();

                $dayConsume->setTotal($this->fetchDayUsage($consumesData, $index, $consume));
                $dayGenerate->setTotal($this->fetchDayUsage($generatesData, $index, $generate));

                if ($includeMonths) {
                    $date = new DateTime();
                    $this->apiHelper->setDatePart($date, 1, $index + 1);
                    $months[] = new MonthUsage($date, $dayConsume, $dayGenerate, []);
                }
            }
        }

        return new YearUsage($year, $consume, $generate, $months);
    }

    public function getRangeUsage(DateTime $startDate, DateTime $endDate, User $user): RangeUsage {
        $currentDate = clone $startDate;
        $this->apiHelper->setDatePart($currentDate, 1);

        $consume = new ZoneUsage();
        $generate = new ZoneUsage();
        $months = [];

        while ($currentDate <= $endDate) {
            $monthData = $this->getMonthUsage(clone $currentDate, $user, false);

            array_push($months, $monthData);

            $consume->addDayUsage($monthData->getConsume()->getDay());
            $consume->addNightUsage($monthData->getConsume()->getNight());
            $generate->addDayUsage($monthData->getGenerate()->getDay());
            $generate->addNightUsage($monthData->getGenerate()->getNight());

            $currentDate->modify('next month');
        }

        return new RangeUsage($startDate, $endDate, $consume, $generate, $months);
    }

    private function fetchData(array $properties, User $user): object {
        if (!$user->getSessionId()) {
            $this->login($user);
        }

        $data = $this->mockBrowserRequest($properties, $user);

        if (null === $data || !property_exists($data, 'dane')) {
            $this->login($user);
            $data = $this->mockBrowserRequest($properties, $user);
        }

        if (null === $data) {
            $this->fetchDataError();
        }

        return $data;
    }

    private function fetchHourUsage(object $data, int $index, ZoneUsage $zoneUsage): float {
        $value = 0;

        if (property_exists($data, $index) && property_exists($data->{$index}, 'EC')) {
            $value = $data->{$index}->EC;

            if ($data->{$index}->Zone === '1') {
                $zoneUsage->addDayUsage($value);
            }

            if ($data->{$index}->Zone === '2') {
                $zoneUsage->addNightUsage($value);
            }
        }

        return $value;
    }

    private function fetchDayUsage(array $data, int $index, ZoneUsage $zoneUsage): float {
        $value = 0;

        if ($data[$index] && property_exists($data[$index], 'suma')) {
            $value = $data[$index]->suma;

            if (property_exists($data[$index], 'tariff1')) {
                $zoneUsage->addDayUsage($data[$index]->tariff1 ?? 0);
            }

            if (property_exists($data[$index], 'tariff2')) {
                $zoneUsage->addNightUsage($data[$index]->tariff2 ?? 0);
            }
        }

        return $value;
    }

    private function getSessionId(ResponseInterface $response): ?string {
        $header = null;

        try {
            $header = $response->getHeaders();
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            return null;
        }

        if (!key_exists('set-cookie', $header)) {
            return null;
        }

        $sessionId = $header['set-cookie'][0];
        $sessionId = explode(';', $sessionId)[0];

        return $sessionId ? explode('=', $sessionId)[1] : null;
    }

    private function mockBrowserRequest(array $properties, User $user): ?object {
        $response = null;
        $data = null;
        $staticProperties = [
            'dane[smartNr]' => $user->getPointId(),
            "dane[checkOZE]" => 'on',
        ];

        try {
            $response = $this->client->request('POST', self::CHARTS_URL, [
                'headers' => ['Cookie' => sprintf('PHPSESSID=%s', $user->getSessionId())],
                'body' => array_merge($properties, $staticProperties)
            ]);
        } catch (TransportExceptionInterface $e) {
            $this->fetchDataError();
        }

        try {
            $data = json_decode($response->getContent());
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $this->fetchDataError();
        }

        return $data;
    }

    private function loginError(): void {
        $problem = new Problem(401, Problem::TYPE_AUTHENTICATION_FAILURE);
        $problem->set('detail', 'Tauron login unsuccessfully');
        throw new ProblemException($problem);
    }

    private function fetchDataError(): void {
        $problem = new Problem(401, Problem::TYPE_FETCH_DATA_ERROR);
        $problem->set('detail', 'Unsuccessfully fetch data, try again later');
        throw new ProblemException($problem);
    }

}
