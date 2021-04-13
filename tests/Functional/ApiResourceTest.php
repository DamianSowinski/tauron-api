<?php

namespace App\Tests\Functional;

use App\Test\CustomApiTestCase;
use DateTime;
use Symfony\Component\HttpFoundation\Response;

class ApiResourceTest extends CustomApiTestCase {

    public function testLoginSuccessfully() {
        $client = self::createClient();
        $this->loginAndSetHeaders($client);
    }

    public function testGetDay() {
        $client = self::createClient();
        $headers = $this->loginAndSetHeaders($client);
        $client->request('GET', '/days/21-03-2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('date', $responseData);
        self::assertArrayHasKey('consume', $responseData);
        self::assertArrayHasKey('generate', $responseData);
        self::assertArrayHasKey('hours', $responseData);
        self::assertEquals('2021-03-21', $responseData['date']);
        self::assertCount(24, $responseData['hours']);
    }

    public function testPassInvalidDate() {
        $client = self::createClient();
        $client->request('GET', '/days/32-03-2021');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetMonth() {
        $client = self::createClient();
        $headers = $this->loginAndSetHeaders($client);
        $client->request('GET', '/months/03-2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('date', $responseData);
        self::assertArrayHasKey('consume', $responseData);
        self::assertArrayHasKey('generate', $responseData);
        self::assertArrayHasKey('days', $responseData);
        self::assertEquals('2021-03', $responseData['date']);
        self::assertCount(31, $responseData['days']);
    }

    public function testPassInvalidMonthDate() {
        $client = self::createClient();
        $client->request('GET', '/months/13-2021');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetYear() {
        $client = self::createClient();
        $headers = $this->loginAndSetHeaders($client);
        $client->request('GET', '/years/2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('year', $responseData);
        self::assertArrayHasKey('consume', $responseData);
        self::assertArrayHasKey('generate', $responseData);
        self::assertArrayHasKey('months', $responseData);
        self::assertEquals('2021', $responseData['year']);
        self::assertCount(12, $responseData['months']);
    }

    public function testPassInvalidYear() {
        $client = self::createClient();
        $client->request('GET', '/years/1967');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $now = new DateTime('now');
        $nextYear = (int)$now->format('Y') + 1;
        $client->request('GET', '/years/' . $nextYear);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetRange() {
        $client = self::createClient();
        $headers = $this->loginAndSetHeaders($client);
        $client->request('GET', '/range?startDate=01-2021&endDate=03-2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('startDate', $responseData);
        self::assertArrayHasKey('endDate', $responseData);
        self::assertArrayHasKey('months', $responseData);
        self::assertEquals('2021-01', $responseData['startDate']);
        self::assertEquals('2021-03', $responseData['endDate']);
        self::assertCount(3, $responseData['months']);
    }

    public function testPassInvalidRange() {
        $client = self::createClient();
        $client->request('GET', '/range');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $client->request('GET', '/range?startDate=02-2021');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $client->request('GET', '/range?startDate=03-2021&endDate=01-2021');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetAll() {
        $client = self::createClient();
        $headers = $this->loginAndSetHeaders($client);
        $client->request('GET', '/all', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('consume', $responseData);
        self::assertArrayHasKey('generate', $responseData);
        self::assertArrayHasKey('years', $responseData);
        self::assertCount(2, $responseData['years']);
    }

    public function testPassInvalidAll() {
        $client = self::createClient();
        $client->request('GET', '/all');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetCollection() {
        $client = self::createClient();
        $headers = $this->loginAndSetHeaders($client);
        $client->request('GET', '/collection?days[]=29-03-2021&days[]=28-03-2021&months[]=03-2021&years[]=2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('days', $responseData);
        self::assertArrayHasKey('months', $responseData);
        self::assertArrayHasKey('years', $responseData);
    }

    public function testPassInvalidCollection() {
        $client = self::createClient();
        $client->request('GET', '/collection');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $headers = $this->loginAndSetHeaders($client);
        $client->request('GET', '/collection?days[]=00-03-2021', [], [], $headers);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function test404Exception() {
        $client = self::createClient();
        $client->request('GET', '/fake-endpoint');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testLoginAsTestUserSuccessfully() {
        $client = self::createClient();
        $this->loginAsTestUserAndSetHeaders($client);
    }

    public function testGetMockDay() {
        $client = self::createClient();
        $headers = $this->loginAsTestUserAndSetHeaders($client);
        $client->request('GET', '/days/21-03-2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('date', $responseData);
        self::assertArrayHasKey('consume', $responseData);
        self::assertArrayHasKey('generate', $responseData);
        self::assertArrayHasKey('hours', $responseData);
        self::assertEquals('2021-03-21', $responseData['date']);
        self::assertCount(24, $responseData['hours']);
    }

    public function testGetMockMonth() {
        $client = self::createClient();
        $headers = $this->loginAsTestUserAndSetHeaders($client);

        $client->request('GET', '/months/02-2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('date', $responseData);
        self::assertArrayHasKey('consume', $responseData);
        self::assertArrayHasKey('generate', $responseData);
        self::assertArrayHasKey('days', $responseData);
        self::assertEquals('2021-02', $responseData['date']);
        self::assertCount(28, $responseData['days']);

        $client->request('GET', '/months/03-2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertEquals('2021-03', $responseData['date']);
        self::assertCount(31, $responseData['days']);
    }

    public function testGetMockYear() {
        $client = self::createClient();
        $headers = $this->loginAsTestUserAndSetHeaders($client);
        $client->request('GET', '/years/2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('year', $responseData);
        self::assertArrayHasKey('consume', $responseData);
        self::assertArrayHasKey('generate', $responseData);
        self::assertArrayHasKey('months', $responseData);
        self::assertEquals('2021', $responseData['year']);
        self::assertCount(12, $responseData['months']);
    }

    public function testGetMockRange() {
        $client = self::createClient();
        $headers = $this->loginAsTestUserAndSetHeaders($client);
        $client->request('GET', '/range?startDate=01-2021&endDate=03-2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('startDate', $responseData);
        self::assertArrayHasKey('endDate', $responseData);
        self::assertArrayHasKey('months', $responseData);
        self::assertEquals('2021-01', $responseData['startDate']);
        self::assertEquals('2021-03', $responseData['endDate']);
        self::assertCount(3, $responseData['months']);
    }

    public function testGetMockAll() {
        $client = self::createClient();
        $headers = $this->loginAsTestUserAndSetHeaders($client);
        $client->request('GET', '/all', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('consume', $responseData);
        self::assertArrayHasKey('generate', $responseData);
        self::assertArrayHasKey('years', $responseData);
        self::assertCount(3, $responseData['years']);
    }

    public function testGetMockCollection() {
        $client = self::createClient();
        $headers = $this->loginAsTestUserAndSetHeaders($client);
        $client->request('GET', '/collection?days[]=29-03-2021&days[]=28-03-2021&months[]=03-2021&years[]=2021', [], [], $headers);

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('days', $responseData);
        self::assertArrayHasKey('months', $responseData);
        self::assertArrayHasKey('years', $responseData);
    }
}

