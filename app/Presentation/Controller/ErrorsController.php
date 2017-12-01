<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 30/10/2017
 * Time: 4:14 PM
 */

namespace Woxapp\Scaffold\Presentation\Controller;

use Phalcon\Mvc\Controller;
use Phalcon\Http\ResponseInterface;
use Woxapp\Scaffold\Utility\ErrorCodes;
use Woxapp\Restful\Exception\RESTException;

/**
 * Class ErrorsController
 * @author Rostislav Shaganenko <foobar76239@gmail.com>
 * @package Woxapp\Scaffold\Presentation\Controller
 */
class ErrorsController extends Controller
{
    /**
     * @var \Phalcon\Config
     */
    protected $configuration;

    /**
     * @var \Woxapp\Scaffold\Presentation\Controller\InternalController
     */
    protected $internalController;

    public function onConstruct()
    {
        $this->configuration = $this->di->get('config');

        $this->internalController = new InternalController();
    }

    public function errorAction(\Throwable $throwable)
    {
        $rawName = $this->dispatcher->getPreviousControllerName();
        $controllerName = "{$this->dispatcher->getPreviousNamespaceName()}\\{$rawName}Controller";
        $previousController = new $controllerName;

        $statusCode = ($throwable instanceof RESTException)
            ? $throwable->getStatusCode()
            : ErrorCodes::INTERNAL_SERVER_ERROR['status'];
        $errorResponse = $this->formErrorResponse($throwable);

        return $this->internalController->optionsAction(
            $previousController->corsMap($this->dispatcher->getPreviousActionName()),
            $this->router->getMatchedRoute()->getHttpMethods()
        )->setJsonContent($errorResponse)->setStatusCode($statusCode);
    }

    public function notFoundAction(): ResponseInterface
    {
        $response = [
            'code' => ErrorCodes::NOT_FOUND['code'],
            'message' => ErrorCodes::NOT_FOUND['message']
        ];

        if ($this->configuration->path('application.debugger') === true) {
            $response['debugger'] = [
                'rewrite_uri' => $this->router->getRewriteUri(),
                'was_matched' => $this->router->wasMatched()
            ];
        }

        return $this->response->setJsonContent($response)
            ->setStatusCode(ErrorCodes::NOT_FOUND['status']);
    }

    protected function formErrorResponse(\Throwable $throwable)
    {
        if ($throwable instanceof RESTException) {
            $apiCode = $throwable->getApiCode();
            $message = $throwable->getMessage();
        } else {
            $apiCode = ErrorCodes::INTERNAL_SERVER_ERROR['code'];
            $message = ErrorCodes::INTERNAL_SERVER_ERROR['message'];
        }

        $error['error'] = [
            'code' => $apiCode,
            'message' => $message
        ];

        if ($this->configuration->path('application.debugger') === true) {
            $error['error']['debugger'] = [
                'class' => get_class($throwable),
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => explode(PHP_EOL, $throwable->getTraceAsString())
            ];
        }

        return $error;
    }
}
