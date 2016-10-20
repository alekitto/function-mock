<?php

namespace Kcs\FunctionMock\Promise;

use Kcs\FunctionMock\FunctionProphecy;

/**
 * Promise interface.
 * Promises are logical blocks, tied to `will...` keyword.
 */
interface PromiseInterface
{
    /**
     * Evaluates promise.
     *
     * @param array          $args
     * @param FunctionProphecy $function
     *
     * @return mixed
     */
    public function execute(array $args, FunctionProphecy $function);
}
