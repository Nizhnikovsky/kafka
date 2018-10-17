<?php
/**
 * Created by PhpStorm.
 * User: Woxapp
 * Date: 03.10.2018
 * Time: 16:47
 */

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /** @var PHPUnit\Framework\MockObject\MockObject */
    private $diMock;

    /** @var PHPUnit\Framework\MockObject\MockObject */
    private $requestMock;

    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->diMock = $this
            ->getMockBuilder(\Phalcon\DiInterface::class)
            ->setMethods(['set', 'setShared', 'remove', 'attempt', 'get',
                'getShared', 'setRaw', 'getRaw', 'getService', 'has',
                'wasFreshInstance', 'getServices', 'setDefault', 'getDefault', 'reset',
                'offsetExists', 'offsetGet', 'offsetSet', 'offsetUnset'
            ])
            ->getMock();

        $this->requestMock = $this
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

        $this->requestMock->method('isMethod')->will($this->returnValue(true));

        $this->diMock->method('getShared')->will($this->returnCallback([$this, 'diCallback']));
    }

    public function testAddRoute()
    {
        $router = new \Woxapp\Scaffold\Presentation\Router\Router();
        $router->setDI($this->diMock);
        $router->addRoute(
            'example',
            \Woxapp\Scaffold\Domain\Usecase\ExampleUseCase::class,
            ['GET', 'OPTIONS'],
            \Woxapp\Scaffold\Presentation\Service\Validation\Rules\ExampleRules::createChatRules()
        );
        $router->handle('example');

        $this->assertContainsOnlyInstancesOf(\Phalcon\Mvc\Router\RouteInterface::class, $router->getRoutes());
        $this->assertEquals(\Woxapp\Scaffold\Presentation\Service\Validation\Rules\ExampleRules::createChatRules(), $router->getValidationRules());
        $this->assertEquals(\Woxapp\Scaffold\Domain\Usecase\ExampleUseCase::class, $router->getUseCaseClass());
    }

    public function diCallback($argument)
    {
        switch ($argument) {
            case 'request':
                return $this->requestMock;
                break;
        }
    }
}
