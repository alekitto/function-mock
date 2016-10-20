<?php

namespace Kcs\FunctionMock\Tests\Prediction;

use Kcs\FunctionMock\FunctionProphecy;
use Kcs\FunctionMock\Prediction\NoCallsPrediction;
use Prophecy\Call\Call;

class NoCallsPredictionTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldNotThrowIfNoCallsArePassed()
    {
        $prediction = new NoCallsPrediction();
        $prediction->check([], $this->prophesize(FunctionProphecy::class)->reveal());

        $this->assertTrue(true);
    }

    /**
     * @expectedException \Kcs\FunctionMock\Exception\Call\UnexpectedCallsException
     */
    public function testShouldThrowIfThereAreCalls()
    {
        $prediction = new NoCallsPrediction();
        $prediction->check([
            new Call(__METHOD__, [], null, null, null, null),
        ], $this->prophesize(FunctionProphecy::class)->reveal());
    }
}
