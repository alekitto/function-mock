<?php

namespace Kcs\FunctionMock;

use Kcs\FunctionMock\Exception\InvalidArgumentException;
use Kcs\FunctionMock\Registry\MockRegistry;
use Prophecy\Argument;
use Prophecy\Prophet;

class FunctionProphecy
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var NamespaceProphecy
     */
    private $namespaceProphecy;

    /**
     * @var Promise\PromiseInterface
     */
    private $promise;

    /**
     * @var array
     */
    private $checkedPredictions = [];

    /**
     * @var Argument\ArgumentsWildcard
     */
    private $argumentsWildcard;

    /**
     * @var Prediction\PredictionInterface
     */
    private $prediction;

    public function __construct(NamespaceProphecy $namespaceProphecy, $functionName, $arguments = null)
    {
        $this->name = $functionName;
        $this->namespaceProphecy = $namespaceProphecy;

        if (! function_exists($functionName)) {
            throw new \Exception();
        }

        $reflectedFunction = new \ReflectionFunction($functionName);
        if (null !== $arguments) {
            $this->withArguments($arguments);
        }

        if (version_compare(PHP_VERSION, '7.0', '>=') && true === $reflectedFunction->hasReturnType()) {
            $type = (string) $reflectedFunction->getReturnType();
            $this->will(function () use ($type) {
                switch ($type) {
                    case 'string': return '';
                    case 'float':  return 0.0;
                    case 'int':    return 0;
                    case 'bool':   return false;
                    case 'array':  return [];

                    case 'callable':
                    case 'Closure':
                        return function () {
                        };

                    case 'Traversable':
                    case 'Generator':
                        $generator = function () {
                            yield;
                        };

                        return $generator();

                    default:
                        $prophet = new Prophet();

                        return $prophet->prophesize($type)->reveal();
                }
            });
        }
    }

    /**
     * Sets argument wildcard.
     *
     * @param array|Argument\ArgumentsWildcard $arguments
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function withArguments($arguments)
    {
        if (is_array($arguments)) {
            $arguments = new Argument\ArgumentsWildcard($arguments);
        }

        if (!$arguments instanceof Argument\ArgumentsWildcard) {
            throw new InvalidArgumentException(sprintf(
                "Either an array or an instance of ArgumentsWildcard expected as\n".
                'a `FunctionProphecy::withArguments()` argument, but got %s.',
                gettype($arguments)
            ));
        }

        $this->argumentsWildcard = $arguments;

        return $this;
    }

    /**
     * Sets custom promise to the prophecy.
     *
     * @param callable|Promise\PromiseInterface $promise
     *
     * @return $this
     *
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function will($promise)
    {
        if (is_callable($promise)) {
            $promise = new Promise\CallbackPromise($promise);
        }

        if (!$promise instanceof Promise\PromiseInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected callable or instance of PromiseInterface, but got %s.',
                gettype($promise)
            ));
        }

        $this->bind();
        $this->promise = $promise;

        return $this;
    }

    /**
     * Sets return promise to the prophecy.
     *
     * @see Promise\ReturnPromise
     *
     * @return $this
     */
    public function willReturn()
    {
        return $this->will(new Promise\ReturnPromise(func_get_args()));
    }

    /**
     * Sets return argument promise to the prophecy.
     *
     * @param int $index The zero-indexed number of the argument to return
     *
     * @see Promise\ReturnArgumentPromise
     *
     * @return $this
     */
    public function willReturnArgument($index = 0)
    {
        return $this->will(new Promise\ReturnArgumentPromise($index));
    }

    /**
     * Sets throw promise to the prophecy.
     *
     * @see Promise\ThrowPromise
     *
     * @param string|\Exception $exception Exception class or instance
     *
     * @return $this
     */
    public function willThrow($exception)
    {
        return $this->will(new Promise\ThrowPromise($exception));
    }

    /**
     * Sets custom prediction to the prophecy.
     *
     * @param callable|Prediction\PredictionInterface $prediction
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function should($prediction)
    {
        if (is_callable($prediction)) {
            $prediction = new Prediction\CallbackPrediction($prediction);
        }

        if (!$prediction instanceof Prediction\PredictionInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected callable or instance of PredictionInterface, but got %s.',
                gettype($prediction)
            ));
        }

        $this->bind();
        $this->prediction = $prediction;

        return $this;
    }

    /**
     * Sets call prediction to the prophecy.
     *
     * @see Prediction\CallPrediction
     *
     * @return $this
     */
    public function shouldBeCalled()
    {
        return $this->should(new Prediction\CallPrediction());
    }

    /**
     * Sets no calls prediction to the prophecy.
     *
     * @see Prediction\NoCallsPrediction
     *
     * @return $this
     */
    public function shouldNotBeCalled()
    {
        return $this->should(new Prediction\NoCallsPrediction());
    }

    /**
     * Sets call times prediction to the prophecy.
     *
     * @see Prediction\CallTimesPrediction
     *
     * @param $count
     *
     * @return $this
     */
    public function shouldBeCalledTimes($count)
    {
        return $this->should(new Prediction\CallTimesPrediction($count));
    }

    /**
     * Checks provided prediction immediately.
     *
     * @param callable|Prediction\PredictionInterface $prediction
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function shouldHave($prediction)
    {
        if (is_callable($prediction)) {
            $prediction = new Prediction\CallbackPrediction($prediction);
        }

        if (!$prediction instanceof Prediction\PredictionInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected callable or instance of PredictionInterface, but got %s.',
                gettype($prediction)
            ));
        }

        if (null === $this->promise) {
            $this->willReturn();
        }

        $calls = $this->namespaceProphecy->findCalls($this->name, $this->getArgumentsWildcard());

        try {
            $prediction->check($calls, $this);
            $this->checkedPredictions[] = $prediction;
        } catch (\Exception $e) {
            $this->checkedPredictions[] = $prediction;

            throw $e;
        }

        return $this;
    }

    /**
     * Checks call prediction.
     *
     * @see Prediction\CallPrediction
     *
     * @return $this
     */
    public function shouldHaveBeenCalled()
    {
        return $this->shouldHave(new Prediction\CallPrediction());
    }

    /**
     * Checks no calls prediction.
     *
     * @see Prediction\NoCallsPrediction
     *
     * @return $this
     */
    public function shouldNotHaveBeenCalled()
    {
        return $this->shouldHave(new Prediction\NoCallsPrediction());
    }

    /**
     * Checks call times prediction.
     *
     * @see Prediction\CallTimesPrediction
     *
     * @param int $count
     *
     * @return $this
     */
    public function shouldHaveBeenCalledTimes($count)
    {
        return $this->shouldHave(new Prediction\CallTimesPrediction($count));
    }

    /**
     * Checks currently registered [with should(...)] prediction.
     */
    public function checkPrediction()
    {
        if (null === $this->prediction) {
            return;
        }

        $this->shouldHave($this->prediction);
    }

    /**
     * Returns currently registered promise.
     *
     * @return null|Promise\PromiseInterface
     */
    public function getPromise()
    {
        return $this->promise;
    }

    /**
     * Returns currently registered prediction.
     *
     * @return null|Prediction\PredictionInterface
     */
    public function getPrediction()
    {
        return $this->prediction;
    }

    /**
     * Returns predictions that were checked on this object.
     *
     * @return Prediction\PredictionInterface[]
     */
    public function getCheckedPredictions()
    {
        return $this->checkedPredictions;
    }

    /**
     * Returns arguments wildcard.
     *
     * @return Argument\ArgumentsWildcard
     */
    public function getArgumentsWildcard()
    {
        return $this->argumentsWildcard;
    }

    private function bind()
    {
        $this->getNamespace()->addProphecy($this);

        $registry = MockRegistry::getInstance();
        if ($registry->has($this->getFQName())) {
            return;
        }

        $registry->register($this);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNamespace()
    {
        return $this->namespaceProphecy;
    }

    public function getFQName()
    {
        return $this->namespaceProphecy->getName().'\\'.$this->name;
    }
}
