<?php

namespace Kcs\FunctionMock\Tests;

use Kcs\FunctionMock\Call\CallCenter;
use Kcs\FunctionMock\Exception\Call\NoCallsException;
use Kcs\FunctionMock\Exception\Call\UnexpectedCallsException;
use Kcs\FunctionMock\FunctionProphecy;
use Kcs\FunctionMock\NamespaceProphecy;
use Prophecy\Argument\ArgumentsWildcard;

class NamespaceProphecyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Kcs\FunctionMock\Exception\InvalidNamespaceException
     */
    public function testConstructionShouldThrowIfGlobalNamespaceIsRequested()
    {
        new NamespaceProphecy('\\');
    }

    public function testCallShouldNotAddProphecyToNamespace()
    {
        $namespace = new NamespaceProphecy(__NAMESPACE__);
        $prophecy = $namespace->time();

        $this->assertNotNull($prophecy);
        $this->assertInstanceOf(FunctionProphecy::class, $prophecy);
        $this->assertCount(0, $namespace->getProphecies());
    }

    public function testCallWithSameArgumentsReturnsTheSameProphecyIfBound()
    {
        $namespace = new NamespaceProphecy(__NAMESPACE__);
        $prophecy = $namespace->time(100, 'asd');

        $namespace->addProphecy($prophecy);

        $this->assertSame($prophecy, $namespace->time(100, 'asd'));
        $this->assertNotSame($prophecy, $namespace->time());
    }

    public function testCallShouldReturnNewProphecyIfDifferentFunctionName()
    {
        $namespace = new NamespaceProphecy(__NAMESPACE__);
        $prophecy = $namespace->time(100, 'asd');

        $namespace->addProphecy($prophecy);

        $this->assertNotSame($prophecy, $namespace->usleep(100, 'asd'));
    }

    public function testShouldForwardCallToCallCenter()
    {
        $callCenter = $this->prophesize(CallCenter::class);
        $namespace = new NamespaceProphecy(__NAMESPACE__, $callCenter->reveal());

        $callCenter->makeCall($namespace, 'time', [])->shouldBeCalled();
        $namespace->call('time', []);
    }

    public function testShouldForwardFindCallsToCallCenter()
    {
        $callCenter = $this->prophesize(CallCenter::class);
        $namespace = new NamespaceProphecy(__NAMESPACE__, $callCenter->reveal());

        $args = new ArgumentsWildcard([]);
        $callCenter->findCalls('time', $args)->shouldBeCalled();
        $namespace->findCalls('time', $args);
    }

    public function testCheckPredictionsShouldCheckAllProphecies()
    {
        $namespace = new NamespaceProphecy(__NAMESPACE__);

        $prophecy1 = $this->prophesize(FunctionProphecy::class);
        $prophecy2 = $this->prophesize(FunctionProphecy::class);

        $prophecy1->checkPrediction()->shouldBeCalled();
        $prophecy2->checkPrediction()->shouldBeCalled();

        $namespace->addProphecy($prophecy1->reveal());
        $namespace->addProphecy($prophecy2->reveal());

        $namespace->checkPredictions();
    }

    public function testCheckPredictionsShouldNotThrowIfAllPredictionsAreCorrect()
    {
        $namespace = new NamespaceProphecy(__NAMESPACE__);

        $prophecy1 = $this->prophesize(FunctionProphecy::class);
        $prophecy2 = $this->prophesize(FunctionProphecy::class);

        $prophecy1->checkPrediction()->willReturn();
        $prophecy2->checkPrediction()->willReturn();

        $namespace->addProphecy($prophecy1->reveal());
        $namespace->addProphecy($prophecy2->reveal());

        $namespace->checkPredictions();
        $this->assertTrue(true);
    }

    /**
     * @expectedException \Prophecy\Exception\Prediction\AggregateException
     * @expectedExceptionMessage No calls  Unexpected
     */
    public function testCheckPredictionsShouldThrowAggregateException()
    {
        $namespace = new NamespaceProphecy(__NAMESPACE__);

        $prophecy1 = $this->prophesize(FunctionProphecy::class);
        $prophecy2 = $this->prophesize(FunctionProphecy::class);

        $prophecy1->checkPrediction()->willThrow(new NoCallsException('No calls'));
        $prophecy2->checkPrediction()->willThrow(new UnexpectedCallsException('Unexpected'));

        $namespace->addProphecy($prophecy1->reveal());
        $namespace->addProphecy($prophecy2->reveal());

        $namespace->checkPredictions();
    }
}
