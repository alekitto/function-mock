<?php

namespace Kcs\FunctionMock\Tests\PhpUnit;

use Kcs\FunctionMock\NamespaceProphecy;
use Kcs\FunctionMock\PhpUnit\FunctionMockTrait;
use Kcs\FunctionMock\Prophet\Prophet;

class BaseTestClass
{
    public $verifyCalled = false;

    protected function verifyMockObjects()
    {
        $this->verifyCalled = true;
    }
}

class TestClass extends BaseTestClass {
    use FunctionMockTrait;

    public function setFunctionMockProphet(Prophet $functionMockProphet)
    {
        $this->functionMockProphet = $functionMockProphet;
    }

    public function prophesize($ncOrClass)
    {
        return $this->prophesizeForFunctions($ncOrClass);
    }

    public function execute()
    {
        $this->verifyMockObjects();
    }
}

class FunctionMockTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldVerifyPredictions()
    {
        $prophet = $this->prophesize(Prophet::class);
        $prophet->checkPredictions()->shouldBeCalled();

        $test = new TestClass;
        $test->setFunctionMockProphet($prophet->reveal());
        $test->execute();

        $this->assertTrue($test->verifyCalled);
    }

    public function testShouldCallVerifyMockObjectIfNoProphetIsSet()
    {
        $test = new TestClass;
        $test->execute();

        $this->assertTrue($test->verifyCalled);
    }

    public function testProphesizeShouldReturnNamespaceProphecy()
    {
        $test = new TestClass();

        $this->assertInstanceOf(NamespaceProphecy::class, $test->prophesize(__CLASS__));
    }
}