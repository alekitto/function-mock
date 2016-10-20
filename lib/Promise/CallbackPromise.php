<?php

namespace Kcs\FunctionMock\Promise;

use Kcs\FunctionMock\FunctionProphecy;

/**
 * Callback promise.
 */
class CallbackPromise implements PromiseInterface
{
    private $callback;

    /**
     * Initializes callback promise.
     *
     * @param callable $callback Custom callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Evaluates promise callback.
     *
     * @param array          $args
     * @param FunctionProphecy $function
     *
     * @return mixed
     */
    public function execute(array $args, FunctionProphecy $function)
    {
        $callback = $this->callback;

        return call_user_func($callback, $args, $function);
    }
}
