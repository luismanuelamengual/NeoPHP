<?php
namespace NeoPHP\Auth;

use RuntimeException;
use NeoPHP\Auth\Authenticator\DefaultAuthenticator;
use NeoPHP\Auth\Authenticator\JWTAuthenticator;
use NeoPHP\Http\Response;
use NeoPHP\Http\Session;
use NeoPHP\Utils\DateUtils;
use NeoPHP\Utils\Strings;
use stdClass;

class AuthController {

    const AUTHORIZATION_HEADER_NAME = "Authorization";
    const S_AUTHORIZATION_SCHEMA = "SAuth";
    const STK_AUTHORIZATION_SCHEMA = "StkAuth";
    const JWT_AUTHORIZATION_SCHEMA = "Bearer";

    public function checkAction() {
        if ($this->verifyRoute()) {
            $request = get_request();
            $session = get_session();
            $authorizationHeader = $request->header(self::AUTHORIZATION_HEADER_NAME);
            if (empty($authorizationHeader)) {
                $authorizationHeader = $request->header(strtolower(self::AUTHORIZATION_HEADER_NAME));
            }
            if (!empty($authorizationHeader)) {
                $headersToken = explode(' ', $authorizationHeader);
                if (sizeof($headersToken) == 2) {
                    switch ($headersToken[0]) {
                        case self::S_AUTHORIZATION_SCHEMA:
                        case self::STK_AUTHORIZATION_SCHEMA:
                            $sessionToken = $headersToken[1];
                            $authenticator = new DefaultAuthenticator($sessionToken);
                            break;
                        case self::JWT_AUTHORIZATION_SCHEMA:
                            $sessionToken = $headersToken[1];
                            $authenticator = new JWTAuthenticator($sessionToken);
                            break;
                    }
                }
            } else {
                $sessionName = $session->name();
                if ($request->has($sessionName)) {
                    $sessionToken = $request->get($sessionName);
                    $authenticator = new DefaultAuthenticator($sessionToken);
                }
                else if ($request->hasCookie($sessionName)) {
                    $sessionToken = $request->cookie($sessionName);
                    $authenticator = new DefaultAuthenticator($sessionToken);
                }
            }
            if (isset($authenticator)) {
                Auth::setAuthenticator($authenticator);
                if ($authenticator->isTokenValid()) {
                    $tokenId = $authenticator->getTokenId();
                    $session->id($tokenId);
                    $session->start();
                    if (!$session->has("tokenKey") || $session->get("tokenKey") != $tokenId) {
                        //Session no iniciada en nodo
                        $sessionData = $authenticator->getTokenData();
                        if (!empty($sessionData)) {
                            $this->completeSession($session, $sessionData);
                        } else {
                            throw new RuntimeException("Authentication token data broken", Response::HTTP_FORBIDDEN);
                        }
                    }
                    $app = get_app();
                    $app->language($session->get("userLanguage"));
                    $app->timeZone(DateUtils::getTimeZoneByOffset($session->get("userTimeZoneOffset")));
                } else {
                    throw new RuntimeException("Authentication Token is expired or Invalid", Response::HTTP_UNAUTHORIZED);
                }
            } else {
                throw new RuntimeException("Authentication Token is missing", Response::HTTP_BAD_REQUEST);
            }
        }
    }

    public function verifyRoute(array $excludeRoutes = null, string $route = null, string  $method = null) : bool {
        $verify = true;
        $method = $method ?? get_request()->method();
        $path = $route ?? get_request()->path();
        if (Strings::startsWith($path, "/")) {
            $path = substr($path, 1);
        }
        $exceptRoutes = $excludeRoutes ?? get_property("auth.except");
        if (!empty($exceptRoutes)) {
            foreach ($exceptRoutes as $exceptRoute) {
                if (is_array($exceptRoute)) {
                    if (Strings::startsWith($exceptRoute["route"],"/")) {
                        $exceptRoute["route"] = substr($exceptRoute["route"], 1);
                    }
                    if ($exceptRoute["route"] == $path && $exceptRoute["method"] == $method) {
                        $verify = false;
                        break;
                    }
                } else {
                    if ($exceptRoute == $path) {
                        $verify = false;
                        break;
                    }
                }
            }
        }
        return $verify;
    }

    protected function completeSession(Session $session, stdClass $data) : Session {
        $session->clear();
        $authData = (array)$data;
        foreach ($authData as $key => $authDataItem) {
            $session->set($key, $authDataItem);
        }
        return $session;
    }

}