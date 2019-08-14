<?php

namespace NeoPHP\Auth\Authenticator;

use Firebase\JWT\JWT;
use stdClass;

class JWTAuthenticator extends Authenticator {

    private $tokenData;

    public function setToken(string $token) : void {
        $this->tokenKey = $token;
        $this->tokenData = JWT::decode($token, $this->getSecrectKey(), $this->getEncode());
        $this->tokenId = $this->tokenData->jti;
        $this->addJWTAccesibilityHeaders();
    }

    public function isTokenValid(): bool {
        $valid = $this->tokenData->aud === $this->getAud();
        $valid = $valid && ( $this->tokenData->exp < time() );
        return $valid;
    }

    public function getTokenData() : stdClass {
        return $this->tokenData;
    }

    private function addJWTAccesibilityHeaders () : void {
        $response = get_response();
        $response->header('Access-Control-Allow-Headers', 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method');
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        $response->header('Allow', 'GET, POST, OPTIONS, PUT, DELETE');
    }

    private function getAud() : string {
        $aud = null;
        $aud = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER["REMOTE_USER"])) {
            $aud .= "__";
            $aud .= $_SERVER["REMOTE_USER"];
        }
        if (!empty($_SERVER["REMOTE_HOST"])) {
            $aud .= "__";
            $aud .= $_SERVER["REMOTE_HOST"];
        }
        return sha1($aud);
    }

    private function getSecrectKey() :string {
        return get_property("auth.jwt.public_key");
    }

    private function getEncode(): array {
        return [ get_property("auth.jwt.encode") ];
    }
}