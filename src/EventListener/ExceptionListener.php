<?php

namespace App\EventListener;

use App\Model\Problem;
use App\Model\ProblemException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener {

    public function __construct() {

    }

    public function onKernelException(ExceptionEvent $event) {

        if ($_SERVER['APP_ENV'] === 'dev' ) {
            return null;
        }

        $exception = $event->getThrowable();
        $statusCode = null;
        $problem = null;

        if ($exception instanceof ProblemException) {
            $problem = $exception->getProblem();
            $statusCode = $exception->getProblem()->getStatusCode();

        } else {
            $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            $problem = new Problem($statusCode);
        }

        $response = new JsonResponse($problem, $statusCode);
        $response->headers->set('Content-Type', 'application/problem+json');

        $event->setResponse($response);
    }
}
