<?php

namespace Kcs\FunctionMock\Prediction;

use Kcs\FunctionMock\FunctionProphecy;
use Prophecy\Call\Call;

/**
 * Prediction interface.
 * Predictions are logical test blocks, tied to `should...` keyword.
 */
interface PredictionInterface
{
    /**
     * Tests that double fulfilled prediction.
     *
     * @param Call[] $calls
     * @param FunctionProphecy $prophecy
     *
     * @throws object
     * @return void
     */
    public function check(array $calls, FunctionProphecy $prophecy);
}
