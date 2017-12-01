<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 30/10/2017
 * Time: 10:16 AM
 */

use Phalcon\Mvc\Router;
use Phalcon\Mvc\RouterInterface;

$router = function (): RouterInterface {
    $router = new Router(false);

    // Define your routes here. Usage of Phalcon's Router\Group is advised for better maintainability.

    $router->notFound(
        [
            /* FIXME: Developers should change this according to the project. */
            'controller' => 'Woxapp\\Scaffold\\Presentation\\Controller\\Errors',
            'action' => 'notFound'
        ]
    );

    return $router;
};
