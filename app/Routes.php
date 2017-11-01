<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 30/10/2017
 * Time: 10:16 AM
 */

use Phalcon\Mvc\Router;
use Phalcon\Mvc\RouterInterface;
use Phalcon\Mvc\Router\Group as RouteGroup;

$router = function (): RouterInterface {
    $router = new Router(false);

    // Define your routes here. Usage of Phalcon's Router\Group is advised for better maintainability.

    $router->notFound(
        [
            'controller' => 'Woxapp\\Scaffold\\Presentation\\Controller\\Errors',
            'action' => 'notFound'
        ]
    );

    return $router;
};
