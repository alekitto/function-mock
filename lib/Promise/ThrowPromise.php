<?php

namespace Kcs\FunctionMock\Promise;

use Doctrine\Instantiator\Instantiator;
use Kcs\FunctionMock\Exception\InvalidArgumentException;
use Kcs\FunctionMock\FunctionProphecy;
use ReflectionClass;

/**
 * Throw promise.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ThrowPromise implements PromiseInterface
{
    private $exception;

    /**
     * @var \Doctrine\Instantiator\Instantiator
     */
    private $instantiator;

    /**
     * Initializes promise.
     *
     * @param string|\Exception $exception Exception class name or instance
     *
     * @throws InvalidArgumentException
     */
    public function __construct($exception)
    {
        if (is_string($exception)) {
            if (!class_exists($exception)
             && 'Exception' !== $exception
             && !is_subclass_of($exception, 'Exception')) {
                throw new InvalidArgumentException(sprintf(
                    'Exception class or instance expected as argument to ThrowPromise, but got %s.',
                    $exception
                ));
            }
        } elseif (!$exception instanceof \Exception) {
            throw new InvalidArgumentException(sprintf(
                'Exception class or instance expected as argument to ThrowPromise, but got %s.',
                is_object($exception) ? get_class($exception) : gettype($exception)
            ));
        }

        $this->exception = $exception;
    }

    /**
     * Throws predefined exception.
     *
     * @param array          $args
     * @param FunctionProphecy $function
     *
     * @throws object
     */
    public function execute(array $args, FunctionProphecy $function)
    {
        if (is_string($this->exception)) {
            $classname = $this->exception;
            $reflection = new ReflectionClass($classname);
            $constructor = $reflection->getConstructor();

            if ($constructor->isPublic() && 0 == $constructor->getNumberOfRequiredParameters()) {
                throw $reflection->newInstance();
            }

            if (!$this->instantiator) {
                $this->instantiator = new Instantiator();
            }

            throw $this->instantiator->instantiate($classname);
        }

        throw $this->exception;
    }
}
