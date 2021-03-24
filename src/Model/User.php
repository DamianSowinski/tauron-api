<?php

namespace App\Model;

class User {
    private string $pointId;
    private string $username;
    private string $password;
    private ?string $sessionId;

    public function __construct(string $pointId, string $username, string $password, ?string $sessionId = null) {
        $this->pointId = $pointId;
        $this->username = $username;
        $this->password = $password;
        $this->sessionId = $sessionId;
    }

    public function getPointId(): string {
        return $this->pointId;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getSessionId(): ?string {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void {
        $this->sessionId = $sessionId;
    }

    public function createToken(): string {
        $encryptAlgorithm = $_ENV['ENCRYPT_ALGORITHM'];

        $plainTxt = sprintf('%s::%s::%s', $this->pointId, $this->username, $this->password);
        $encodedTxt = null;

        if (in_array($encryptAlgorithm, openssl_get_cipher_methods())) {
            $encodedTxt = openssl_encrypt($plainTxt, $encryptAlgorithm, $_ENV['ENCRYPT_KEY'], 0, $_ENV['ENCRYPT_INIT_VECTOR']);
        }

        return $encodedTxt;
    }

    static function createFromJSON($jsonData): User {

        if (!isset($jsonData->pointId) || !isset($jsonData->username) || !isset($jsonData->password)) {
            $problem = new Problem(401, Problem::TYPE_VALIDATION_ERROR);
            $problem->set('detail', 'Missing a required user field. Required fields: pointId, username and password');
            throw new ProblemException($problem);
        }

        return new User($jsonData->pointId, $jsonData->username, $jsonData->password);
    }

    static function createFromToken($encodeTxt): User {
        $decodeTXT = openssl_decrypt($encodeTxt, $_ENV['ENCRYPT_ALGORITHM'], $_ENV['ENCRYPT_KEY'], 0, $_ENV['ENCRYPT_INIT_VECTOR']);
        $data = explode('::', $decodeTXT);

        if (count($data) < 3) {
            $problem = new Problem(401, Problem::TYPE_AUTHENTICATION_FAILURE);
            $problem->set('detail', 'Token is invalid, please try login again');
            throw new ProblemException($problem);
        }

        return new User($data[0], $data[1], $data[2]);
    }

}
