<?php

namespace Kcs\FunctionMock\Promise;

use Kcs\FunctionMock\FunctionProphecy;

/**
 * Return promise.
 */
class ReturnPromise implements PromiseInterface
{
    private $returnValues = [];

    /**
     * Initializes promise.
     *
     * @param array $returnValues Array of values
     */
    public function __construct(array $returnValues)
    {
        $this->returnValues = $returnValues;
    }

    /**
     * Returns saved values one by one until last one, then continuously returns last value.
     *
     * @param array          $args
     * @param FunctionProphecy $function
     *
     * @return mixed
     */
    public function execute(array $args, FunctionProphecy $function)
    {
        $value = array_shift($this->returnValues);

        if (!count($this->returnValues)) {
            $this->returnValues[] = $value;
        }

        return $value;
    }
}
