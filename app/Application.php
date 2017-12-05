<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 30/10/2017
 * Time: 2:29 PM
 */

namespace Woxapp\Scaffold;

use Phalcon\Di;
use Phalcon\Http\ResponseInterface;

class Application
{
    /**
     * @var \Phalcon\Di
     */
    private $di;

    /**
     * @var \Phalcon\Mvc\Router
     */
    private $router;

    /**
     * @var \Phalcon\Dispatcher
     */
    private $dispatcher;

    public function __construct(Di $container)
    {
        $this->di = $container;

        $this->router = $this->di->get('router');

        $this->dispatcher = $this->di->get('dispatcher');

        set_error_handler([$this, 'errorHandler']);
    }

    public function dispatch(): ResponseInterface
    {
        $this->router->handle();

        $this->dispatcher->setNamespaceName(
            $this->router->getNamespaceName()
        );

        $this->dispatcher->setControllerName(
            $this->router->getControllerName()
        );

        $this->dispatcher->setActionName(
            $this->router->getActionName()
        );

        $this->dispatcher->setParams(
            $this->router->getParams()
        );

        $this->dispatcher->dispatch();

        $response = $this->dispatcher->getReturnedValue();

        if (!$response instanceof ResponseInterface) {
            throw new \RuntimeException(
                "Controller '{$this->router->getControllerName()}' at namespace '{$this->router->getNamespaceName()}' "
                ."must return a ResponseInterface instance."
            );
        }

        return $response;
    }

    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline)
    {
        if (!(error_reporting() & $errno)) {
            return;
        }

        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}
