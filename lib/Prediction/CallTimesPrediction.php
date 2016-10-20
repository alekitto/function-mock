<?php

namespace Kcs\FunctionMock\Prediction;

use Kcs\FunctionMock\Exception\Call\UnexpectedCallsCountException;
use Kcs\FunctionMock\FunctionProphecy;
use Prophecy\Argument\ArgumentsWildcard;
use Prophecy\Argument\Token\AnyValuesToken;
use Prophecy\Call\Call;
use Prophecy\Util\StringUtil;

class CallTimesPrediction implements PredictionInterface
{
    private $times;
    private $util;

    /**
     * Initializes prediction.
     *
     * @param int        $times
     * @param StringUtil $util
     */
    public function __construct($times, StringUtil $util = null)
    {
        $this->times = intval($times);
        $this->util = $util ?: new StringUtil();
    }

    /**
     * Tests that there was exact amount of calls made.
     *
     * @param Call[]         $calls
     * @param FunctionProphecy $prophecy
     *
     * @throws UnexpectedCallsCountException
     */
    public function check(array $calls, FunctionProphecy $prophecy)
    {
        if ($this->times == count($calls)) {
            return;
        }

        $methodCalls = $prophecy->getNamespace()->findCalls(
            $prophecy->getName(),
            new ArgumentsWildcard([new AnyValuesToken()])
        );

        if (count($calls)) {
            $message = sprintf(
                "Expected exactly %d calls that match:\n".
                "  %s(%s)\n".
                "but %d were made:\n%s",

                $this->times,
                $prophecy->getName(),
                $prophecy->getArgumentsWildcard(),
                count($calls),
                $this->util->stringifyCalls($calls)
            );
        } elseif (count($methodCalls)) {
            $message = sprintf(
                "Expected exactly %d calls that match:\n".
                "  %s(%s)\n".
                "but none were made.\n".
                "Recorded `%s(...)` calls:\n%s",

                $this->times,
                $prophecy->getName(),
                $prophecy->getArgumentsWildcard(),
                $prophecy->getName(),
                $this->util->stringifyCalls($methodCalls)
            );
        } else {
            $message = sprintf(
                "Expected exactly %d calls that match:\n".
                "  %s(%s)\n".
                'but none were made.',

                $this->times,
                $prophecy->getName(),
                $prophecy->getArgumentsWildcard()
            );
        }

        throw new UnexpectedCallsCountException($message);
    }
}
