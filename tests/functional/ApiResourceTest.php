<?php

namespace App\Tests\functional;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiResourceTest extends WebTestCase {

    public function testLoginSuccessfully() {
        $client = self::createClient();

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $body = $_ENV['TAURON_LOGIN_DATA'];

        $client->request('POST', '/login', [], [], $headers, $body);
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('token', $responseData);
    }

    public function testGetDay() {
        $client = self::createClient();

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $body = $_ENV['TAURON_LOGIN_DATA'];

        $client->request('POST', '/login', [], [], $headers, $body);
        $content = json_decode($client->getResponse()->getContent());
        $token = null;

        if (isset($content->token)) {
            $token = $content->token;
        }

        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => $token];

        $client->request('GET', '/days/21-03-2021', [], [], $headers);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPassInvalidDate() {
        $client = self::createClient();

        $client->request('GET', '/days/32-03-2021');
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetMonth() {
        $client = self::createClient();

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $body = $_ENV['TAURON_LOGIN_DATA'];

        $client->request('POST', '/login', [], [], $headers, $body);

        $content = json_decode($client->getResponse()->getContent());
        $token = null;

        if (isset($content->token)) {
            $token = $content->token;
        }

        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => $token];

        $client->request('GET', '/months/03-2020', [], [], $headers);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPassInvalidMonthDate() {
        $client = self::createClient();

        $client->request('GET', '/months/13-2021');
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetYear() {
        $client = self::createClient();

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $body = $_ENV['TAURON_LOGIN_DATA'];

        $client->request('POST', '/login', [], [], $headers, $body);

        $content = json_decode($client->getResponse()->getContent());
        $token = null;

        if (isset($content->token)) {
            $token = $content->token;
        }

        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => $token];

        $client->request('GET', '/years/2021', [], [], $headers);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPassInvalidYear() {
        $client = self::createClient();

        $client->request('GET', '/years/1967');
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $now = new DateTime('now');
        $nextYear = (int)$now->format('Y') + 1;

        $client->request('GET', '/years/' . $nextYear);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetRange() {
        $client = self::createClient();

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $body = $_ENV['TAURON_LOGIN_DATA'];

        $client->request('POST', '/login', [], [], $headers, $body);

        $content = json_decode($client->getResponse()->getContent());
        $token = null;

        if (isset($content->token)) {
            $token = $content->token;
        }

        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => $token];

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
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $client->request('GET', '/range?startDate=02-2021');
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $client->request('GET', '/range?startDate=03-2021&endDate=01-2021');
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetAll() {
        $client = self::createClient();

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $body = $_ENV['TAURON_LOGIN_DATA'];

        $client->request('POST', '/login', [], [], $headers, $body);

        $content = json_decode($client->getResponse()->getContent());
        $token = null;

        if (isset($content->token)) {
            $token = $content->token;
        }

        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => $token];

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
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

    }

    public function testGetCollection() {
        $client = self::createClient();

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $body = $_ENV['TAURON_LOGIN_DATA'];

        $client->request('POST', '/login', [], [], $headers, $body);

        $content = json_decode($client->getResponse()->getContent());
        $token = null;

        if (isset($content->token)) {
            $token = $content->token;
        }

        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => $token];

        $client->request('GET', '/collection?days[]=29-03-2021&days[]=28-03-2021&months[]=03-2021&years[]=2021', [], [], $headers );
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
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');

        $headers = ['CONTENT_TYPE' => 'application/json'];
        $body = $_ENV['TAURON_LOGIN_DATA'];

        $client->request('POST', '/login', [], [], $headers, $body);

        $content = json_decode($client->getResponse()->getContent());
        $token = null;

        if (isset($content->token)) {
            $token = $content->token;
        }

        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => $token];

        $client->request('GET', '/collection?days[]=00-03-2021', [], [], $headers);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
    }


    public function test404Exception() {
        $client = self::createClient();

        $client->request('GET', '/fake-endpoint');
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

}

