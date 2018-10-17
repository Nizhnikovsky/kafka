<?php
/**
 * Created by PhpStorm.
 * User: Woxapp
 * Date: 03.10.2018
 * Time: 16:47
 */

use PHPUnit\Framework\TestCase;

class CorsListenerTest extends TestCase
{

    /** @var \Phalcon\DiInterface */
    private $di;

    private $eventMock;

    public function setUp()
    {
        $this->di = \Phalcon\Di::getDefault();
        $this->eventMock = $this->getMockBuilder(Phalcon\Events\Event::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDiInstanceOfDiInterface()
    {
        $this->assertInstanceOf(\Phalcon\DiInterface::class, $this->di);
    }

    public function testDiContainNeededServices()
    {
        $this->assertTrue($this->di->has('request'));
        $this->assertTrue($this->di->has('response'));
        $this->assertTrue($this->di->has('router'));
        $this->assertTrue($this->di->has('config'));
        $this->assertTrue($this->di->has('dispatcher'));
    }

    public function testBeforeExecute()
    {
        $corsListener = new \Woxapp\Scaffold\Presentation\EventListener\CorsListener();
        $corsListener->beforeExecuteRoute($this->eventMock, $this->di->get('dispatcher'));
        $this->assertNotEmpty($this->di->get('response')->getHeaders());
    }
}