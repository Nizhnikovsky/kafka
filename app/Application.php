<?php

namespace Woxapp\Scaffold;

use Phalcon\DiInterface;
use Phalcon\Http\ResponseInterface;
use Woxapp\Restful\Exception\RESTException;
use Woxapp\Scaffold\Domain\Interfaces\InteractorInterface;
use Woxapp\Scaffold\Presentation\Router\Router;
use Woxapp\Scaffold\Presentation\Service\RequestHandler;
use Woxapp\Scaffold\Utility\ErrorCodes;

class Application
{
    /**
     * @var DiInterface
     */
    private $di;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var \Phalcon\Dispatcher
     */
    private $dispatcher;

    public function __construct(DiInterface $container)
    {
        $this->di = $container;
        $this->router = $this->di->get('router');
        $this->dispatcher = $this->di->get('dispatcher');

        set_error_handler([$this, 'errorHandler']);
    }

    public function dispatch(): ResponseInterface
    {
        $this->router->handle();

        $response = $this->execute();
        if (!$response instanceof ResponseInterface) {
            throw new \RuntimeException("UseCase '{$this->router->getUseCaseClass()}' must return a ResponseInterface instance.");
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

    private function exceptionHandler(\Throwable $throwable)
    {
        $code = ($throwable instanceof RESTException)
            ? $throwable->getStatusCode()
            : ErrorCodes::INTERNAL_SERVER_ERROR['status'];
        $content = $this->formErrorResponse($throwable);

        return $this->setResponse($content, $code);
    }

    private function formErrorResponse(\Throwable $throwable)
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

        if ($this->di->get('config')->path('application.debugger') === true) {
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

    private function routeNotFoundHandler()
    {
        $content = [
            'code' => ErrorCodes::NOT_FOUND['code'],
            'message' => ErrorCodes::NOT_FOUND['message']
        ];

        return $this->setResponse($content, ErrorCodes::NOT_FOUND['status']);
    }

    private function execute()
    {
        if (!$this->router->wasMatched()) {
            return $this->routeNotFoundHandler();
        }

        try {
            $requestHandler = new RequestHandler($this->di->get('request'));
            $requestHandler->validateRequest($this->router->getValidationRules());

            $useCaseClass = $this->router->getUseCaseClass();
            if (!\in_array(InteractorInterface::class, \class_implements($useCaseClass), true)) {
                throw new \InvalidArgumentException("The '$useCaseClass' must implement InteractorInterface");
            }

            /** @var InteractorInterface $useCase */
            $useCase = new $useCaseClass();
            $useCase->setDI($this->di);
            $args = [$requestHandler->getHeaders(), $requestHandler->getBody(), $this->dispatcher->getParams()];
            $useCase->process(...$args);

            return $this->setResponse($useCase->output(), 200);
        } catch (\Throwable $throwable) {
            return $this->exceptionHandler($throwable);
        }
    }

    private function setResponse($content, int $code)
    {
        return $this->di->get('response')
            ->setJsonContent($content)
            ->setStatusCode($code);
    }
}
