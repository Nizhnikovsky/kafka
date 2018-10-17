<?php
/**
 * Created by PhpStorm.
 * User: Woxapp
 * Date: 03.10.2018
 * Time: 16:47
 */

use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    /** @var PHPUnit\Framework\MockObject\MockObject */
    private $di;

    /** @var PHPUnit\Framework\MockObject\MockObject */
    private $router;

    /** @var PHPUnit\Framework\MockObject\MockObject */
    private $response;

    /** @var PHPUnit\Framework\MockObject\MockObject */
    private $request;

    /** @var PHPUnit\Framework\MockObject\MockObject */
    private $dispatcher;

    /** @var PHPUnit\Framework\MockObject\MockObject */
    private $config;

    public function setUp()
    {
        $this->di = $this->createMock(\Phalcon\DiInterface::class);
        $this->router = $this->createMock(\Woxapp\Scaffold\Presentation\Router\Router::class);
        $this->response = $this->createMock(\Phalcon\Http\ResponseInterface::class);
        $this->request = $this
            ->getMockBuilder(\Phalcon\Http\RequestInterface::class)
            ->setMethods([
                'get', 'getHeaders', 'getBody', 'getPost', 'getQuery', 'getServer', 'has', 'hasPost',
                'hasPut', 'hasQuery', 'hasServer', 'getHeader', 'getScheme', 'isAjax', 'isSoapRequested',
                'isSecureRequest', 'getRawBody', 'getServerAddress', 'getServerName', 'getHttpHost',
                'getPort', 'getClientAddress', 'getMethod', 'getUserAgent', 'isMethod', 'isPost',
                'isGet', 'isPut', 'isHead', 'isDelete', 'isOptions', 'isPurge', 'isTrace', 'isConnect',
                'hasFiles', 'getUploadedFiles', 'getHTTPReferer', 'getAcceptableContent', 'getBestAccept',
                'getClientCharsets', 'getBestCharset', 'getLanguages', 'getBestLanguage', 'getBasicAuth',
                'getDigestAuth', 'getJsonRawBody'
            ])
            ->getMock();
        $this->config = $this->createMock(Phalcon\Config::class);
        $this->dispatcher = $this->getMockForAbstractClass(\Phalcon\Dispatcher::class, [], '', true, true, true, ['getParams']);
    }

    public function testDispatchNotFound()
    {
        $this->di->method('get')->will($this->returnCallback([$this, 'diCallbackNotFounded']));

        $application = new \Woxapp\Scaffold\Application($this->di);
        $this->assertInstanceOf(\Phalcon\Http\ResponseInterface::class, $application->dispatch());
    }

    public function testDispatchSuccess()
    {
        $this->di->method('get')->will($this->returnCallback([$this, 'diCallbackSuccess']));

        $application = new \Woxapp\Scaffold\Application($this->di);
        $this->assertInstanceOf(\Phalcon\Http\ResponseInterface::class, $application->dispatch());
    }

    public function testDispatchExceptionHandle()
    {
        $this->di->method('get')->will($this->returnCallback([$this, 'diCallbackExceptionHandle']));

        $application = new \Woxapp\Scaffold\Application($this->di);
        $this->assertInstanceOf(\Phalcon\Http\ResponseInterface::class, $application->dispatch());
    }

    public function testDispatchRuntimeException()
    {
        $this->expectException(\RuntimeException::class);

        $this->di->method('get')->will($this->returnCallback([$this, 'diCallbackRuntimeException']));

        $application = new \Woxapp\Scaffold\Application($this->di);
        $this->assertInstanceOf(\Phalcon\Http\ResponseInterface::class, $application->dispatch());
    }

    public function diCallbackNotFounded($argument)
    {
        switch ($argument) {
            case 'router': return $this->getRouter(false);
            case 'response': return $this->getResponse();
            case 'dispatcher': return $this->getDispatcher();
        }
    }

    public function diCallbackRuntimeException($argument)
    {
        switch ($argument) {
            case 'router': return $this->getRouter(false);
            case 'response':
                $this->response->method('setJsonContent')->will($this->returnSelf());
                $this->response->method('setStatusCode')->will($this->returnValue(new stdClass()));
                return $this->response;
            case 'dispatcher': return $this->getDispatcher();
        }
    }

    public function diCallbackSuccess($argument)
    {
        switch ($argument) {
            case 'router':
                $this->getRouter();
                $this->router->method('getValidationRules')->will($this->returnValue([]));
                $this->router->method('getUseCaseClass')->will($this->returnValue(\Woxapp\Scaffold\Domain\Usecase\ExampleUseCase::class));
                return $this->router;
            case 'response': return $this->getResponse();
            case 'request': return $this->getRequest();
            case 'dispatcher': return $this->getDispatcher();
            default: return $this->getConfig();
        }
    }

    public function diCallbackExceptionHandle($argument)
    {
        switch ($argument) {
            case 'router': return $this->getRouter();
            case 'response': return $this->getResponse();
            case 'request': return $this->getRequest();
            case 'dispatcher': return $this->getDispatcher();
            default: return $this->getConfig();
        }
    }

    private function getResponse()
    {
        $this->response->method('setJsonContent')->will($this->returnSelf());
        $this->response->method('setStatusCode')->will($this->returnSelf());

        return $this->response;
    }

    private function getRequest()
    {
        $this->request->method('getQuery')->will($this->returnValue([]));
        $this->request->method('getMethod')->will($this->returnValue('GET'));
        $this->request->method('getHeaders')->will($this->returnValue(['Authorization' => 'Bearer 123456']));
        $this->request->method('getBody')->will($this->returnValue([]));

        return $this->request;
    }

    private function getRouter($matched = true)
    {
        $this->router->method('handle')->will($this->returnValue(null));
        $this->router->method('wasMatched')->will($this->returnValue($matched));

        return $this->router;
    }

    private function getDispatcher()
    {
        $this->dispatcher->method('getParams')->will($this->returnValue([]));

        return $this->dispatcher;
    }

    private function getConfig()
    {
        $this->config->method('path')->will($this->returnValue(false));

        return $this->config;
    }
}
