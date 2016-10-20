<?php

namespace Kcs\FunctionMock\Prediction;

use Kcs\FunctionMock\FunctionProphecy;
use Prophecy\Call\Call;

class CallbackPrediction implements PredictionInterface
{
    private $callback;

    /**
     * Initializes callback prediction.
     *
     * @param callable $callback Custom callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Executes preset callback.
     *
     * @param Call[]         $calls
     * @param FunctionProphecy $prophecy
     */
    public function check(array $calls, FunctionProphecy $prophecy)
    {
        $callback = $this->callback;
        call_user_func($callback, $calls, $prophecy);
    }
}
