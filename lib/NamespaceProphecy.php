<?php

namespace Kcs\FunctionMock;

use Kcs\FunctionMock\Call\CallCenter;
use Kcs\FunctionMock\Exception\InvalidNamespaceException;
use Prophecy\Argument\ArgumentsWildcard;
use Prophecy\Comparator\Factory;
use Prophecy\Exception\Prediction\AggregateException;
use Prophecy\Exception\Prediction\PredictionException;
use Prophecy\Prophecy\Revealer;
use Prophecy\Prophecy\RevealerInterface;
use SebastianBergmann\Comparator\ComparisonFailure;

class NamespaceProphecy
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var FunctionProphecy[]
     */
    private $prophecies;

    /**
     * @var Factory
     */
    private $comparatorFactory;

    /**
     * @var CallCenter
     */
    private $callCenter;

    /**
     * @var RevealerInterface
     */
    private $revealer;

    /**
     * Mock constructor.
     *
     * @param string $name Target namespace
     * @param CallCenter $callCenter
     * @param RevealerInterface $revealer
     */
    public function __construct($name, CallCenter $callCenter = null, RevealerInterface $revealer = null)
    {
        if (empty($name) || $name === '\\') {
            throw new InvalidNamespaceException('Cannot mock functions in global namespace');
        }

        $this->name = $name;
        $this->callCenter = $callCenter ?: new CallCenter();
        $this->revealer = $revealer ?: new Revealer();

        $this->comparatorFactory = Factory::getInstance();
        $this->prophecies = [];
    }

    public function findCalls($functionName, ArgumentsWildcard $argumentsWildcard)
    {
        return $this->callCenter->findCalls($functionName, $argumentsWildcard);
    }

    public function call($name, array $args)
    {
        return $this->callCenter->makeCall($this, $name, $args);
    }

    /**
     * Checks that registered method predictions do not fail.
     *
     * @throws \Prophecy\Exception\Prediction\AggregateException If any of registered predictions fail
     */
    public function checkPredictions()
    {
        $exception = new AggregateException(sprintf("%s:\n", $this->name));

        foreach ($this->prophecies as $prophecy) {
            try {
                $prophecy->checkPrediction();
            } catch (PredictionException $e) {
                $exception->append($e);
            }
        }

        if (count($exception->getExceptions())) {
            throw $exception;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getProphecies()
    {
        return $this->prophecies;
    }

    public function addProphecy(FunctionProphecy $prophecy)
    {
        $this->prophecies[] = $prophecy;
    }

    public function __call($name, $arguments)
    {
        $arguments = new ArgumentsWildcard($this->revealer->reveal($arguments));

        foreach ($this->getProphecies() as $prophecy) {
            if ($prophecy->getName() !== $name) {
                continue;
            }

            $argumentsWildcard = $prophecy->getArgumentsWildcard();
            $comparator = $this->comparatorFactory->getComparatorFor(
                $argumentsWildcard, $arguments
            );

            try {
                $comparator->assertEquals($argumentsWildcard, $arguments);

                return $prophecy;
            } catch (ComparisonFailure $failure) {
            }
        }

        return new FunctionProphecy($this, $name, $arguments);
    }
}
