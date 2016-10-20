<?php

namespace Kcs\FunctionMock\Tests\Call;

use Kcs\FunctionMock\Call\CallCenter;
use Kcs\FunctionMock\NamespaceProphecy;
use Kcs\FunctionMock\Registry\MockRegistry;
use Prophecy\Argument;
use Prophecy\Argument\ArgumentsWildcard;

class CallCenterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CallCenter
     */
    private $callCenter;

    public function setUp()
    {
        $this->callCenter = new CallCenter();
    }

    public function tearDown()
    {
        MockRegistry::getInstance()->unregisterAll();
    }

    public function testMakeCallShouldCorrectlyStoreFileAndLine()
    {
        $namespace = new NamespaceProphecy(__NAMESPACE__, $this->callCenter);
        $namespace->time()->willReturn(100);

        time();

        $calls = $this->callCenter->findCalls('time', new ArgumentsWildcard([]));

        $this->assertCount(1, $calls);
        $this->assertEquals(__LINE__ - 5, $calls[0]->getLine());
        $this->assertEquals(__FILE__, $calls[0]->getFile());
    }

    public function testMakeCallShouldSelectHigherScore()
    {
        $namespace = new NamespaceProphecy(__NAMESPACE__, $this->callCenter);
        $namespace->time(100)->willReturn(100);
        $namespace->time(Argument::type('int'))->will(function ($args) {
            return $args[0];
        });
        $namespace->time(Argument::cetera())->willThrow(new \Exception());
        $namespace->sleep(100)->willThrow(new \Exception());

        $this->assertEquals(100, time(100));
        $this->assertEquals(20, time(20));
    }

    /**
     * @expectedException \Kcs\FunctionMock\Exception\Call\UnexpectedCallException
     */
    public function testMakeCallShouldThrowIfFunctionIsCalledWithWrongArguments()
    {
        $namespace = new NamespaceProphecy(__NAMESPACE__, $this->callCenter);
        $namespace->time(100)->willReturn(100);

        time();
    }

    /**
     * @expectedException \BadFunctionCallException
     */
    public function testMakeCallShouldThrowIfWillThrowIsCalled()
    {
        $namespace = new NamespaceProphecy(__NAMESPACE__, $this->callCenter);
        $namespace->time()->willThrow(new \BadFunctionCallException());

        time();
    }
}
