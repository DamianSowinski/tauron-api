<?php

namespace App\Test;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomApiTestCase extends WebTestCase {

    protected function loginAndSetHeaders(KernelBrowser $client): array {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        $body = $_ENV['TAURON_LOGIN_DATA'];

        $client->request('POST', '/login', [], [], $headers, $body);
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('token', $responseData);

        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => $responseData['token']];

        return $headers;
    }

    protected function loginAsTestUserAndSetHeaders(KernelBrowser $client): array {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        $body = $_ENV['TAURON_TEST_LOGIN_DATA'];

        $client->request('POST', '/login', [], [], $headers, $body);
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertArrayHasKey('token', $responseData);

        $headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => $responseData['token']];

        return $headers;
    }


}
