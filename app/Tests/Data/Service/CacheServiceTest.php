<?php

use PHPUnit\Framework\TestCase;

class CacheServiceTest extends TestCase
{
    /** @var PHPUnit\Framework\MockObject\MockObject */
    private $memcached;

    /** @var \Woxapp\Scaffold\Data\Service\CacheService */
    private $service;

    public function setUp()
    {
        $this->memcached = $this->createMock(\Memcached::class);
        $this->service = new \Woxapp\Scaffold\Data\Service\CacheService($this->memcached);
    }

    public function testCacheKeys()
    {
        $this->service->setCacheKeys('key1');
        $this->assertContains('key1', $this->service->getCacheKeys());
    }

    public function testTags()
    {
        $this->service->addTag('tag1');
        $this->assertContains('tag1', $this->service->getTags());
    }

    public function testMethod()
    {
        $this->service->setMethod('articles');
        $this->assertSame('articles', $this->service->getMethod());
    }

    public function testWithoutMethod()
    {
        $this->expectException(\Exception::class);

        $this->assertSame('articles', $this->service->getMethod());
    }

    public function testSingleKeyData()
    {
        $this->assertSame($this->service, $this->service->setMethod('method'));

        // set
        $this->memcached
            ->expects($this->once())
            ->method('set')
            ->with('method', ['key' => 'value']);
        $this->assertTrue($this->service->save(['key' => 'value']));

        // get
        $this->memcached
            ->expects($this->once())
            ->method('get')
            ->with('method')
            ->will($this->returnValue(['key' => 'value']));
        $this->assertSame(['key' => 'value'], $this->service->get());
   }

    public function testDeleteSingleKeyData()
    {
        $this->assertSame($this->service, $this->service->setMethod('method'));

        $this->memcached
            ->expects($this->once())
            ->method('delete')
            ->with('method')
            ->will($this->returnValue(true));
        $this->assertTrue($this->service->delete());
    }

    public function testNestedKeyData()
    {
        $this->assertSame($this->service, $this->service->setMethod('method'));
        $this->assertSame($this->service, $this->service->setCacheKeys('key1'));

        // set
        $this->memcached
            ->expects($this->once())
            ->method('set')
            ->with('method', ['key1' => ['key' => 'value']]);
        $this->assertTrue($this->service->save(['key' => 'value']));

        // get
        $this->memcached
            ->expects($this->once())
            ->method('get')
            ->with('method')
            ->will($this->returnValue(['key1' => ['key' => 'value']]));
        $this->assertSame(['key' => 'value'], $this->service->get());
    }

    public function testDeleteNestedKeyData()
    {
        $this->assertSame($this->service, $this->service->setMethod('method'));
        $this->assertSame($this->service, $this->service->setCacheKeys('key1'));

        $this->memcached
            ->expects($this->exactly(2))
            ->method('get')
            ->with('method')
            ->will($this->returnValue(['key1' => ['key' => 'value']]));
        $this->memcached
            ->expects($this->once())
            ->method('set')
            ->with('method', []);
        $this->assertTrue($this->service->delete());
    }

    public function testSingleDataWithTags()
    {
        $this->assertSame($this->service, $this->service->setMethod('method'));
        $this->assertSame($this->service, $this->service->addTag('tag1'));

        // set
        $this->memcached
            ->expects($this->at(0))
            ->method('set')
            ->with('method', ['key' => 'value']);
        $this->memcached
            ->expects($this->at(1))
            ->method('get')
            ->with('TAGS')
            ->will($this->returnValue(['tag2' => []]));
        $this->memcached
            ->expects($this->at(2))
            ->method('set')
            ->with('TAGS', [
                'tag1' => [md5(serialize(['method' => []])) => ['method' => 'method', 'keys' => []]],
                'tag2' => [],
            ]);
        $this->assertTrue($this->service->save(['key' => 'value']));

        // get
        $this->memcached
            ->expects($this->at(0))
            ->method('get')
            ->with('method')
            ->will($this->returnValue(['key' => 'value']));
        $this->assertSame(['key' => 'value'], $this->service->get());
    }

    public function testDeleteDataByTag()
    {
        $this->memcached
            ->expects($this->once())
            ->method('get')
            ->with('TAGS')
            ->will($this->returnValue(['tag1' => []]));
        $this->memcached
            ->expects($this->once())
            ->method('set')
            ->with('TAGS', []);
        $this->service->deleteByTag('tag1');
    }
}
