<?php
/**
 * Created by PhpStorm.
 * User: Woxapp
 * Date: 03.10.2018
 * Time: 16:47
 */

use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    public function testAddRoute()
    {
        $group = new \Woxapp\Scaffold\Presentation\Router\Group();
        $group->setPrefix('api/v1');
        $group->addRoute(
            'example',
            \Woxapp\Scaffold\Domain\Usecase\ExampleUseCase::class,
            ['GET', 'OPTIONS'],
            \Woxapp\Scaffold\Presentation\Service\Validation\Rules\ExampleRules::createChatRules()
        );

        $this->assertCount(1, $group->getRoutes());
        $this->assertEquals('api/v1', $group->getPrefix());
    }
}
