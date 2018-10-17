<?php
/**
 * Created by PhpStorm.
 * User: Woxapp
 * Date: 01.10.2018
 * Time: 16:39
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class RequestHandlerTest extends TestCase
{

    /**
     * @var PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    public function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Phalcon\Http\RequestInterface::class)
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
    }

    /**
     * @expectedException Woxapp\Restful\Presentation\Exception\PresentationException
     */
    public function testAuthorizationHeadersNotValid()
    {
        $this->requestMock->method('getHeaders')->will($this->returnValue(['Authorization' => '63453454353']));

        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);
        $handler->getHeaders();
    }

    /**
     * @expectedException Woxapp\Restful\Presentation\Exception\PresentationException
     */
    public function testAuthorizationHeadersEmpty()
    {
        $this->requestMock->method('getHeaders')->will($this->returnValue(['Amount' => '10']));

        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);
        $handler->getHeaders();
    }

    public function testBearerAuthorizationHeaderValid()
    {
        $this->requestMock->method('getHeaders')->will($this->returnValue(['Authorization' => 'Bearer 63453454353']));

        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);
        $headers = $handler->getHeaders();

        $this->assertInternalType('array', $headers);
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertInternalType('array', $headers['Authorization']);
        $this->assertCount(1, $headers['Authorization']);
        $this->assertArrayHasKey('Bearer', $headers['Authorization']);
        $this->assertEquals(63453454353, $headers['Authorization']['Bearer']);
    }

    public function testApiKeyAuthorizationHeaderValid()
    {
        $this->requestMock->method('getHeaders')->will($this->returnValue(['Authorization' => 'Key 63453454353']));

        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);
        $headers = $handler->getHeaders();

        $this->assertInternalType('array', $headers);
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertInternalType('array', $headers['Authorization']);
        $this->assertCount(1, $headers['Authorization']);
        $this->assertArrayHasKey('Key', $headers['Authorization']);
        $this->assertEquals(63453454353, $headers['Authorization']['Key']);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testBodyUnknownMethod()
    {
        $this->requestMock->method('getMethod')->will($this->returnValue('GOT'));
        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);

        $handler->getBody();
    }

    public function testBodyGetMethod()
    {
        $this->requestMock->method('getMethod')->will($this->returnValue('GET'));
        $this->requestMock->method('getQuery')->will($this->returnValue(['_url' => 'http://google.com', 'amount' => 10]));
        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);

        $result = $handler->getBody();

        $this->assertArrayNotHasKey('_url', $result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertCount(1, $result);
    }

    public function testBodyPutMethod()
    {
        $data = ['amount' => 10, 'id' => 77];
        $this->requestMock->method('getMethod')->will($this->returnValue('PUT'));
        $this->requestMock->method('getJsonRawBody')->will($this->returnValue($data));
        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);

        $result = $handler->getBody();

        $this->assertArrayHasKey('amount', $result);
        $this->assertEquals($data, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertCount(2, $result);
    }

    public function testBodyDeleteMethod()
    {
        $data = ['amount' => 10, 'id' => 77];
        $this->requestMock->method('getMethod')->will($this->returnValue('DELETE'));
        $this->requestMock->method('getJsonRawBody')->will($this->returnValue($data));
        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);

        $result = $handler->getBody();

        $this->assertArrayHasKey('amount', $result);
        $this->assertEquals($data, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertCount(2, $result);
    }

    public function testBodyPostMethod()
    {
        $postData = ['name' => 10, 'phone' => '+381823818'];

        $this->requestMock->method('getMethod')->will($this->returnValue('POST'));
        $this->requestMock->method('getPost')->will($this->returnValue($postData));
        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);

        $result = $handler->getBody();
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('phone', $result);
        $this->assertEquals($postData, $result);
        $this->assertCount(2, $result);
    }

    public function testBodyRawPostMethod()
    {
        $rawData = ['amount' => 10, 'id' => 77];
        $this->requestMock->method('getMethod')->will($this->returnValue('POST'));
        $this->requestMock->method('getJsonRawBody')->will($this->returnValue($rawData));
        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);

        $result = $handler->getBody();
        $this->assertArrayHasKey('amount', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($rawData, $result);
        $this->assertCount(2, $result);
    }

    public function testValidateAllIsValid()
    {
        $this->requestMock->method('getHeaders')->will($this->returnValue(['Authorization' => 'Bearer 63453454353', 'count' => 5]));
        $this->requestMock->method('getMethod')->will($this->returnValue('POST'));
        $this->requestMock->method('getJsonRawBody')->will($this->returnValue(['message' => 'some message']));
        $handler = new \Woxapp\Scaffold\Presentation\Service\RequestHandler($this->requestMock);
        $this->assertCount(1, $handler->getBody());
        $this->assertCount(2, $handler->getHeaders());
        $this->assertEquals(['message' => 'some message'], $handler->getBody());

        $handler->validateRequest($this->validationRules());
    }

    private function validationRules(): array
    {
        return [
            'required_headers' => ['Authorization', 'count'],
            'required_params' => ['message'],
            'rules' => [
                'message' => new Symfony\Component\Validator\Constraints\Length(['min' => 1, 'max' => 255]),
                'phone' => new Symfony\Component\Validator\Constraints\Length(['min' => 1, 'max' => 255]),
                'count' => new \Symfony\Component\Validator\Constraints\LessThanOrEqual(7)
            ],
        ];
    }
}