<?php

namespace Kcs\FunctionMock\PhpUnit;

use Kcs\FunctionMock\NamespaceProphecy;
use Kcs\FunctionMock\Prophet\Prophet;

/**
 * Use FunctionMock in PHPUnit test case: the easy way
 *
 * Just "use" this inside your \PHPUnit_Framework_TestCase
 * and call prophesizeForFunctions method to mock functions
 * in your tested class' namespace.
 *
 * It automatically checks predictions at the end of
 * each test and unregisters all the mocked functions
 */
trait FunctionMockTrait
{
    /**
     * FunctionMock Prophet.
     * Used to create namespace prophecies and
     * prediction checking.
     *
     * DO NOT USE IT DIRECTLY!
     * Please use prophesizeForFunctions instead
     *
     * @var Prophet
     * @internal
     */
    private $functionMockProphet = null;

    /**
     * Create a Prophecy for the given namespace
     * If a class name is passed, the prophecy for its
     * namespace will be returned
     *
     * @param string $namespaceOrClass
     *
     * @return NamespaceProphecy
     */
    protected function prophesizeForFunctions($namespaceOrClass)
    {
        return $this->getFunctionMockProphet()->prophesize($namespaceOrClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function verifyMockObjects()
    {
        parent::verifyMockObjects();

        if (null !== $this->functionMockProphet) {
            $this->functionMockProphet->checkPredictions();
        }
    }

    /**
     * Get a Prophet object
     *
     * DO NOT USE IT DIRECTLY!
     * Please use prophesizeForFunctions instead
     *
     * @return Prophet
     * @internal
     */
    private function getFunctionMockProphet()
    {
        if (null === $this->functionMockProphet) {
            $this->functionMockProphet = new Prophet();
        }

        return $this->functionMockProphet;
    }
}
