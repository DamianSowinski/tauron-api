<?php

namespace App\Model;

use InvalidArgumentException;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response;

class Problem implements JsonSerializable {
    const TYPE_VALIDATION_ERROR = 'validation_error';
    const TYPE_FETCH_DATA_ERROR = 'fetch_data_error';

    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';

    const TYPE_SERIALIZATION_ERROR = 'serialization_error';
    const TYPE_AUTHENTICATION_FAILURE = 'authentication_failure';
    const TYPE_SESSION_EXPIRED = 'session_expired';
    const TYPE_ACCESS_DENIED = 'access_denied';

    private static array $titles = array(
        self::TYPE_VALIDATION_ERROR => 'There was a validation error',
        self::TYPE_FETCH_DATA_ERROR => 'There was a fetch data error',

        self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',

        self::TYPE_SERIALIZATION_ERROR => 'There was a serialization error',
        self::TYPE_AUTHENTICATION_FAILURE => 'Authentication failure',
        self::TYPE_SESSION_EXPIRED => 'Session expired',
        self::TYPE_ACCESS_DENIED => 'Access denied'
    );

    private string $statusCode;
    private ?string $type;
    private string $title;
    private array $extraData = [];

    public function __construct(string $statusCode, ?string $type = null) {
        $this->statusCode = $statusCode;
        $this->type = $type;

        if (!$type) {
            $this->type = 'about:blank';
            $this->title = Response::$statusTexts[$statusCode] ?? 'Unknown HTTP status code :(';
        } else {
            if (!isset(self::$titles[$type])) {
                throw new InvalidArgumentException('No title for type ' . $type);
            }

            $this->title = self::$titles[$type];
        }
    }

    public function getStatusCode(): string {
        return $this->statusCode;
    }

    public function set($name, $value): void {
        $this->extraData[$name] = $value;
    }

    public function jsonSerialize(): array {
        return array_merge(
            [
                'status' => $this->statusCode,
                'type' => $this->type,
                'title' => $this->title,
            ],
            $this->extraData
        );
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function addDocumentUrlToType(string $siteUrl) {
        if ($this->type != 'about:blank') {
            $this->type = $siteUrl . '/docs/errors#' . $this->type;
        }
    }
}
