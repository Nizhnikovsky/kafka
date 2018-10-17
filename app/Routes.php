<?php

use Woxapp\Scaffold\Presentation\Router\Router;
use Woxapp\Scaffold\Presentation\Router\Group as RouteGroup;
use Phalcon\Mvc\RouterInterface;

$router = function (): RouterInterface {
    $router = new Router(false);

    $routes = new RouteGroup();
    $routes->setPrefix('/api/v1/');

    // Define your routes here. Usage of Phalcon's Router\Group is advised for better maintainability.
    $routes->addRoute(
        'example',
        \Woxapp\Scaffold\Domain\Usecase\ExampleUseCase::class,
        ['GET', 'OPTIONS'],
        \Woxapp\Scaffold\Presentation\Service\Validation\Rules\ExampleRules::createChatRules()
    );

    return $router->mount($routes);
};

$di->setShared('router', $router);
