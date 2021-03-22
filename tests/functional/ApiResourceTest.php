<?php

namespace App\Tests\functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiResourceTest extends WebTestCase {
    /**
     * @dataProvider provideUrls
     * @param string $url
     */
    public function testEndpointsExistApp(string $url) {
        $client = self::createClient();

        $client->request('GET', $url);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testGetDay() {
        $client = self::createClient();

        $client->request('GET', '/days/21-03-2021');
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPassInvalidDate() {
        $client = self::createClient();

        $client->request('GET', '/days/32-03-2021');
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetMonth() {
        $client = self::createClient();

        $client->request('GET', '/months/03-2020');
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPassInvalidMonthDate() {
        $client = self::createClient();

        $client->request('GET', '/months/13-2021');
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetYear() {
        $client = self::createClient();

        $client->request('GET', '/years/2021');
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPassInvalidYear() {
        $client = self::createClient();

        $client->request('GET', '/years/sss');
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetRange() {
        $client = self::createClient();

        $client->request('GET', '/range');
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testGetCollection() {
        $client = self::createClient();

        $client->request('GET', '/collection');
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testLogin() {
        $client = self::createClient();

        $client->request('GET', '/login');
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function test404Exception() {
        $client = self::createClient();

        $client->request('GET', '/fake-endpoint');
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function provideUrls(): array {
        return [
            ['/'],
            ['/days/21-03-2021'],
            ['/months/03-2021'],
            ['/years/2021'],
            ['/range?from=01-01-2021&to=21-03-2021'],
            ['/collection'],
            ['/login'],
        ];
    }
}

