<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 31/10/2017
 * Time: 10:03 AM
 */

namespace Woxapp\Scaffold\Presentation\Controller;

use Phalcon\Dispatcher;
use Phalcon\Mvc\Controller;
use Woxapp\Scaffold\Utility\ErrorCodes;
use Woxapp\Restful\Presentation\Exception\PresentationException;

/**
 * Class AbstractController
 * @author Rostislav Shaganenko <foobar76239@gmail.com>
 * @package Woxapp\Scaffold\Presentation\Controller
 */
abstract class AbstractController extends Controller
{
    /**
     * @var \Phalcon\Config
     */
    protected $config;

    abstract public function corsMap(string $action): array;

    public function onConstruct()
    {
        $this->config = $this->di->get('config');
    }

    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
        if ($this->request->getMethod() === 'OPTIONS') {
            $dispatcher->forward(
                [
                    'namespace' => 'Woxapp\\Scaffold\\Presentation\\Controller',
                    'controller' => 'Internal',
                    'action' => "options",
                    'params' => [
                        'headers' => $this->corsMap($this->router->getActionName()),
                        'methods' => $this->router->getMatchedRoute()->getHttpMethods()
                    ]
                ]
            );

            return false;
        }

        return true;
    }

    protected function getBody()
    {
        switch ($this->request->getMethod()) {
            case 'GET':
                return ($this->request->getQuery() === null) ? [] : $this->request->getQuery();
            case 'POST':
            case 'PUT':
            case 'DELETE':
                return ($this->request->getJsonRawBody() === null) ? [] : $this->request->getJsonRawBody(true);
            default:
                throw new \BadMethodCallException(
                    "Could not get request body, method '{$this->request->getMethod()}' is not supported "
                    ."by this controller."
                );
        }
    }

    protected function getHeaders(): array
    {
        $headers = $this->request->getHeaders();
        $headers['Authorization'] = $this->parseAuthorizationHeader($headers);
        return $headers;
    }

    protected function parseAuthorizationHeader(array $headers): array
    {
        if (!array_key_exists('Authorization', $headers)
            && strpos($headers['Authorization'], 'Key') === false
            && strpos($headers['Authorization'], 'Bearer') === false) {
            throw new PresentationException(ErrorCodes::REQUEST_HEADERS_MALFORMED, ['Authorization']);
        }

        $exploded = explode(' ', $headers['Authorization']);

        return [$exploded[0] => $exploded[1]];
    }
}
