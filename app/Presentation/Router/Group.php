<?php

namespace Woxapp\Scaffold\Presentation\Router;

use Phalcon\Mvc\Router\RouteInterface;

class Group extends \Phalcon\Mvc\Router\Group
{
    public function addRoute(string $pattern, string $useCaseClass, array $httpMethods = [], array $validationRules = []): RouteInterface
    {
        $pattern = $this->getPrefix() ? ($this->getPrefix() . $pattern) : $pattern;

        $route = new Route($pattern, $useCaseClass, $httpMethods, $validationRules);
        $this->_routes[] = $route;

        return $route;
    }
}
