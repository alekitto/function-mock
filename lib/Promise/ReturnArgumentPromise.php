<?php

namespace Kcs\FunctionMock\Promise;

use Kcs\FunctionMock\Exception\InvalidArgumentException;
use Kcs\FunctionMock\FunctionProphecy;

/**
 * Return argument promise.
 */
class ReturnArgumentPromise implements PromiseInterface
{
    /**
     * @var int
     */
    private $index;

    /**
     * Initializes callback promise.
     *
     * @param int $index The zero-indexed number of the argument to return
     *
     * @throws InvalidArgumentException
     */
    public function __construct($index = 0)
    {
        if (!is_int($index) || $index < 0) {
            throw new InvalidArgumentException(sprintf(
                'Zero-based index expected as argument to ReturnArgumentPromise, but got %s.',
                $index
            ));
        }
        $this->index = $index;
    }

    /**
     * Returns nth argument if has one, null otherwise.
     *
     * @param array          $args
     * @param FunctionProphecy $function
     *
     * @return null|mixed
     */
    public function execute(array $args, FunctionProphecy $function)
    {
        return count($args) > $this->index ? $args[$this->index] : null;
    }
}
