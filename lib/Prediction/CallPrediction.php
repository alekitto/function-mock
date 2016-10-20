<?php

namespace Kcs\FunctionMock\Prediction;

use Kcs\FunctionMock\Exception\Call\NoCallsException;
use Kcs\FunctionMock\FunctionProphecy;
use Prophecy\Argument\ArgumentsWildcard;
use Prophecy\Argument\Token\AnyValuesToken;
use Prophecy\Call\Call;
use Prophecy\Util\StringUtil;

class CallPrediction implements PredictionInterface
{
    private $util;

    /**
     * Initializes prediction.
     *
     * @param StringUtil $util
     */
    public function __construct(StringUtil $util = null)
    {
        $this->util = $util ?: new StringUtil();
    }

    /**
     * Tests that there was at least one call.
     *
     * @param Call[]         $calls
     * @param FunctionProphecy $prophecy
     *
     * @throws NoCallsException
     */
    public function check(array $calls, FunctionProphecy $prophecy)
    {
        if (count($calls)) {
            return;
        }

        $methodCalls = $prophecy->getNamespace()->findCalls($prophecy->getName(), new ArgumentsWildcard([new AnyValuesToken()]));

        if (count($methodCalls)) {
            throw new NoCallsException(sprintf(
                "No calls have been made that match:\n".
                "  %s(%s)\n".
                "but expected at least one.\n".
                "Recorded `%s(...)` calls:\n%s",

                $prophecy->getName(),
                $prophecy->getArgumentsWildcard(),
                $prophecy->getName(),
                $this->util->stringifyCalls($methodCalls)
            ));
        }

        throw new NoCallsException(sprintf(
            "No calls have been made that match:\n".
            "  %s(%s)\n".
            'but expected at least one.',

            $prophecy->getName(),
            $prophecy->getArgumentsWildcard()
        ));
    }
}
