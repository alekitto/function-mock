<?php

namespace Kcs\FunctionMock\Tests\Prediction;

use Kcs\FunctionMock\FunctionProphecy;
use Kcs\FunctionMock\Prediction\CallbackPrediction;
use Prophecy\Call\Call;

class CallbackPredictionTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldCallCallback()
    {
        $called = false;
        $prediction = new CallbackPrediction(function () use (&$called) {
            $called = true;
        });

        $prophecy = $this->prophesize(FunctionProphecy::class);
        $call = new Call(__METHOD__, [], null, null, '', 0);
        $prediction->check([$call], $prophecy->reveal());

        $this->assertTrue($called);
    }
}
