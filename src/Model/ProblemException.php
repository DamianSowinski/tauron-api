<?php

namespace App\Model;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProblemException extends HttpException {
    private Problem $problem;

    public function __construct(Problem $problem, Exception $previous = null, array $headers = [], $code = 0) {
        $this->problem = $problem;

        parent::__construct(
            $problem->getStatusCode(),
            $problem->getTitle(),
            $previous,
            $headers,
            $code
        );
    }

    public function getProblem(): Problem {
        return $this->problem;
    }
}
