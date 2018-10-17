<?php

namespace Woxapp\Scaffold\Presentation\Router;

use Phalcon\Mvc\Router\RouteInterface;

class Router extends \Phalcon\Mvc\Router
{
    public function addRoute(string $pattern, string $useCaseClass, array $httpMethods = [], array $validationRules = []): RouteInterface
    {
        $route = new Route($pattern, $useCaseClass, $httpMethods, $validationRules);
        $this->_routes[] = $route;

        return $route;
    }

    public function getUseCaseClass(): ?string
    {
        /** @var Route $route */
        $route = $this->getMatchedRoute();

        return $this->wasMatched() ? $route->getUseCaseClass() : null;
    }

    public function getValidationRules(): array
    {
        /** @var Route $route */
        $route = $this->getMatchedRoute();

        return $this->wasMatched() ? $route->getValidationRules() : [];
    }
}
