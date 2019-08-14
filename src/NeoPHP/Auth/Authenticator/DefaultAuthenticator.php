<?php

namespace NeoPHP\Auth\Authenticator;

use NeoPHP\Http\Response;
use NeoPHP\Resources\Resources;
use NeoPHP\Utils\Strings;
use RuntimeException;
use stdClass;
use Throwable;

class DefaultAuthenticator extends Authenticator {

    private $signature;

    private $timestamp;

    public function setToken(string $token) : void {
        if (Strings::contains($token, ",")) {
            $this->decodeDataSignature($token);
        } else {
            $this->tokenId = $token;
        }
        $this->tokenKey = $this->tokenId;
    }

    private function decodeDataSignature($data) : bool {
        $authorizationDataTokens = explode(',', $data);
        foreach ($authorizationDataTokens as $authorizationDataToken) {
            try {
                list($authorizationParameter, $authorizationValue) = explode('=', $authorizationDataToken, 2);
            }
            catch (Throwable $ex) {
                throw new RuntimeException("Signature \"$data\" is invalid", Response::HTTP_BAD_REQUEST);
            }
            if (Strings::startsWith($authorizationValue, '"') && Strings::endsWith($authorizationValue, '"')) {
                $authorizationValue = substr($authorizationValue, 1, strlen($authorizationValue) - 2);
            }
            switch ($authorizationParameter) {
                case "session":
                    $this->tokenId = $authorizationValue;
                    break;
                case "signature":
                    $this->signature = $authorizationValue;
                    break;
                case "timestamp":
                    $this->timestamp = doubleval($authorizationValue);
                    break;
                default:
                    throw new RuntimeException("Signature parameter \"$authorizationParameter\" is not supported", Response::HTTP_BAD_REQUEST);
                    break;
            }
        }
        return isset($this->tokenId) && isset($this->signature) && isset($this->timestamp);
    }

    public function isTokenValid(): bool {
        $query = Resources::node(get_property("auth.node"))->get("auth")
            ->select('tokenKey', 'activityDate')
            ->where("tokenKey", $this->tokenId);
        if (isset($this->signature) && isset($this->timestamp)) {
            $query->where("signature", $this->signature)
                ->where("timestamp", $this->timestamp);
        }
        return !empty($query->find());
    }

    public function getTokenData() : stdClass {
        $data = Resources::node(get_property("auth.node"))->get("auth")
            ->where("tokenKey", $this->tokenId)
            ->find();
        return $data;
    }

}