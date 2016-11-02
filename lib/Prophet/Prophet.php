<?php

namespace Kcs\FunctionMock\Prophet;

use Kcs\FunctionMock\NamespaceProphecy;
use Kcs\FunctionMock\Registry\MockRegistry;
use Prophecy\Exception\Prediction\AggregateException;
use Prophecy\Exception\Prediction\PredictionException;

class Prophet
{
    /**
     * @var NamespaceProphecy[]
     */
    private $prophecies = [];

    public function prophesize($namespaceOrClass)
    {
        if (class_exists($namespaceOrClass) || interface_exists($namespaceOrClass)) {
            $reflectedClass = new \ReflectionClass($namespaceOrClass);
            $namespaceOrClass = $reflectedClass->getNamespaceName();
        }

        if (isset($this->prophecies[$namespaceOrClass])) {
            return $this->prophecies[$namespaceOrClass];
        }

        $prophecy = new NamespaceProphecy($namespaceOrClass);

        return $this->prophecies[$prophecy->getName()] = $prophecy;
    }

    /**
     * Check all prophecies for predictions
     *
     * @throws AggregateException
     */
    public function checkPredictions()
    {
        $exception = new AggregateException("Some predictions failed:\n");
        foreach ($this->prophecies as $prophecy) {
            try {
                $prophecy->checkPredictions();
            } catch (PredictionException $e) {
                $exception->append($e);
            }
        }

        $this->prophecies = [];
        MockRegistry::getInstance()->unregisterAll();

        if (count($exception->getExceptions())) {
            throw $exception;
        }

    }
}
