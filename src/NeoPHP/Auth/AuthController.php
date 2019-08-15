<?php
namespace NeoPHP\Auth;

use NeoPHP\Http\Response;
use NeoPHP\Utils\DateUtils;
use NeoPHP\Utils\Strings;
use RuntimeException;

class AuthController {

    public function checkAction() {
        if ($this->verifyRoute()) {
            $authenticators = Auth::getRegisteredAuthenticators();
            foreach ($authenticators as $authenticator) {
                if ($authenticator->authenticate()) {
                    Auth::setActiveAuthenticator($authenticator);
                    break;
                }
            }

            if (Auth::getActiveAuthenticator() != null) {
                $data = $authenticator->getData();

                if (get_property("auth.use_session", false)) {
                    $tokenId = $authenticator->getTokenId();
                    $session = get_session();
                    $session->id($tokenId);
                    $session->start();
                    $tokenIdPropertyName = get_property("auth.token_id_property_name", "tokenId");
                    if (!$session->has($tokenIdPropertyName) || $session->get($tokenIdPropertyName) != $tokenId) {
                        foreach ($data as $key => $value) {
                            $session->set($key, $value);
                        }
                    }
                }

                $app = get_app();
                $languagePropertyName = get_property("auth.language_property_name", "language");
                if (!empty($data[$languagePropertyName])) {
                    $app->language($data[$languagePropertyName]);
                }
                $timezoneOffsetPropertyName = get_property("auth.timezone_offset_property_name", "timezoneOffset");
                if (!empty($data[$timezoneOffsetPropertyName])) {
                    $app->timeZone(DateUtils::getTimeZoneByOffset($data[$timezoneOffsetPropertyName]));
                }
            } else {
                throw new RuntimeException("Authentication failed", Response::HTTP_BAD_REQUEST);
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
}