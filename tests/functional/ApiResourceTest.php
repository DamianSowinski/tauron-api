<?php

namespace App\Tests\functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiResourceTest extends WebTestCase {

    public function testGetHomeData() {

        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

    }
}
