<?php

namespace NeoPHP\Routing;

interface RouteActionGenerator  {

    public function generateAction($method, array $path);
}
