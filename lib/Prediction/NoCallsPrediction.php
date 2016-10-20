<?php

namespace Kcs\FunctionMock\Prediction;

use Kcs\FunctionMock\Exception\Call\UnexpectedCallsException;
use Kcs\FunctionMock\FunctionProphecy;
use Prophecy\Call\Call;
use Prophecy\Util\StringUtil;

class NoCallsPrediction implements PredictionInterface
{
    private $util;

    /**
     * Initializes prediction.
     *
     * @param null|StringUtil $util
     */
    public function __construct(StringUtil $util = null)
    {
        $this->util = $util ?: new StringUtil();
    }

    /**
     * Tests that there were no calls made.
     *
     * @param Call[]         $calls
     * @param FunctionProphecy $prophecy
     *
     * @throws UnexpectedCallsException
     */
    public function check(array $calls, FunctionProphecy $prophecy)
    {
        if (!count($calls)) {
            return;
        }

        $verb = count($calls) === 1 ? 'was' : 'were';

        throw new UnexpectedCallsException(sprintf(
            "No calls expected that match:\n".
            "  %s(%s)\n".
            "but %d %s made:\n%s",
            $prophecy->getName(),
            $prophecy->getArgumentsWildcard(),
            count($calls),
            $verb,
            $this->util->stringifyCalls($calls)
        ));
    }
}
