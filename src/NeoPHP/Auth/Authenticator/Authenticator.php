<?php
namespace NeoPHP\Auth\Authenticator;

use stdClass;

abstract class Authenticator {

    protected $tokenId;
    protected $tokenKey;

    public function __construct(string $token = null) {
        if (!empty($token)) {
            $this->setToken($token);
        }
    }

    public abstract function setToken(string $token) : void;

    public function getTokenKey() : string {
        return $this->tokenKey;
    }

    public function getTokenId() : string {
        return $this->tokenId;
    }

    public abstract function isTokenValid() : bool;

    public abstract function getTokenData() : stdClass;
}