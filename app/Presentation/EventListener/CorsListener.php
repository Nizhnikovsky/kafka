<?php

namespace Woxapp\Scaffold\Presentation\EventListener;

use Phalcon\Di;
use Phalcon\Events\Event;
use Phalcon\Di\Injectable;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;

class CorsListener extends Injectable
{
    /**
     * @var string
     */
    private $origin;

    /**
     * @param Event $event
     * @param Dispatcher $dispatcher
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        /** @var Di $di */
        $di = $dispatcher->getDI();
        /** @var Request $request */
        $request = $di->get('request');
        /** @var Response $response */
        $response = $di->get('response');
        /** @var Router $router */
        $router = $di->get('router');

        $this->origin = $di->get('config')->path('application.links.origin');

        if ($this->isCorsRequest($request)) {
            $methods = $router->getMatchedRoute() ? (array)$router->getMatchedRoute()->getHttpMethods() : [];
            $response
                ->setHeader('Access-Control-Allow-Origin', $this->origin)
                ->setHeader('Access-Control-Allow-Methods', implode(', ', $methods))
                ->setHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization')
                ->setHeader('Access-Control-Allow-Credentials', 'true');
        }

        if ($this->isPreflightRequest($request)) {
            $response->setStatusCode(200);
            $response->setJsonContent(['available' => true]);
            $response->send();
            exit;
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isCorsRequest(Request $request)
    {
        return $this->origin && !$this->isSameHost($request);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isPreflightRequest(Request $request)
    {
        return $this->isCorsRequest($request) && $request->getMethod() === 'OPTIONS';
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isSameHost(Request $request)
    {
        return $this->origin === $this->getSchemeAndHttpHost($request);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function getSchemeAndHttpHost(Request $request)
    {
        return $request->getScheme() . '://' . $request->getHttpHost();
    }
}
