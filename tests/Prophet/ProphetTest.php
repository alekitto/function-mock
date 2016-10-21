<?php

namespace Kcs\FunctionMock\Tests\Prophet;

use Kcs\FunctionMock\Exception\Call\NoCallsException;
use Kcs\FunctionMock\FunctionProphecy;
use Kcs\FunctionMock\Prophet\Prophet;
use Kcs\FunctionMock\Registry\MockRegistry;

class ProphetTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        MockRegistry::getInstance()->unregisterAll();
    }

    public function testProphesizeShouldReturnProphecyOfClassNamespace()
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize(__CLASS__);

        $this->assertEquals(__NAMESPACE__, $prophecy->getName());
    }

    public function testProphesizeShouldReturnSameProphecyIfCalledForSameNamespace()
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize(__CLASS__);

        $this->assertEquals($prophecy, $prophet->prophesize(__NAMESPACE__));
        $this->assertEquals($prophecy, $prophet->prophesize(self::class));
    }

    public function testCheckPredictionsShouldCheckAllNamespaces()
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize(__CLASS__);

        $function = $this->prophesize(FunctionProphecy::class);
        $function->checkPrediction()->shouldBeCalled();

        $prophecy->addProphecy($function->reveal());
        $prophet->checkPredictions();
    }

    /**
     * @expectedException \Prophecy\Exception\Prediction\AggregateException
     */
    public function testCheckPredictionsShouldWillThrowIfOneThrows()
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize(__CLASS__);

        $function = $this->prophesize(FunctionProphecy::class);
        $function->checkPrediction()->shouldBeCalled();
        $prophecy->addProphecy($function->reveal());

        $function = $this->prophesize(FunctionProphecy::class);
        $function->checkPrediction()->willThrow(new NoCallsException());
        $prophecy->addProphecy($function->reveal());

        $prophet->checkPredictions();
    }
}
