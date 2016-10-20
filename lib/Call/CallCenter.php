<?php

namespace Kcs\FunctionMock\Call;

use Kcs\FunctionMock\Exception\Call\UnexpectedCallException;
use Kcs\FunctionMock\FunctionProphecy;
use Kcs\FunctionMock\NamespaceProphecy;
use Prophecy\Argument\ArgumentsWildcard;
use Prophecy\Call\Call;
use Prophecy\Util\StringUtil;

class CallCenter
{
    private $util;

    /**
     * @var Call[]
     */
    private $recordedCalls = [];

    /**
     * Initializes call center.
     *
     * @param StringUtil $util
     */
    public function __construct(StringUtil $util = null)
    {
        $this->util = $util ?: new StringUtil();
    }

    /**
     * Makes and records specific method call for object prophecy.
     *
     * @param NamespaceProphecy $prophecy
     * @param string $name
     * @param array $arguments
     *
     * @return mixed Returns null if no promise for prophecy found or promise return value.
     *
     * @throws UnexpectedCallException If no appropriate method prophecy found
     * @throws \Exception
     */
    public function makeCall(NamespaceProphecy $prophecy, $name, array $arguments)
    {
        // For efficiency exclude 'args' from the generated backtrace
        // Limit backtrace to last 3 calls as we don't use the rest
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);

        $file = $line = null;
        if (isset($backtrace[3]) && isset($backtrace[3]['file'])) {
            $file = $backtrace[3]['file'];
            $line = $backtrace[3]['line'];
        }

        // There are method prophecies, so it's a fake/stub. Searching prophecy for this call
        $matches = [];
        foreach ($prophecy->getProphecies() as $methodProphecy) {
            if ($methodProphecy->getName() !== $name) {
                continue;
            }

            if (0 < $score = $methodProphecy->getArgumentsWildcard()->scoreArguments($arguments)) {
                $matches[] = [$score, $methodProphecy];
            }
        }

        // If fake/stub doesn't have method prophecy for this call - throw exception
        if (!count($matches)) {
            throw $this->createUnexpectedCallException($prophecy, $name, $arguments);
        }

        // Sort matches by their score value
        @usort($matches, function ($match1, $match2) {
            return $match2[0] - $match1[0];
        });

        // If Highest rated method prophecy has a promise - execute it or return null instead
        $returnValue = null;
        $exception = null;
        if ($promise = $matches[0][1]->getPromise()) {
            try {
                $returnValue = $promise->execute($arguments, $matches[0][1]);
            } catch (\Exception $e) {
                $exception = $e;
            }
        }

        $this->recordedCalls[] = new Call($name, $arguments, $returnValue, $exception, $file, $line);

        if (null !== $exception) {
            throw $exception;
        }

        return $returnValue;
    }

    /**
     * Searches for calls by method name & arguments wildcard.
     *
     * @param string            $functionName
     * @param ArgumentsWildcard $wildcard
     *
     * @return Call[]
     */
    public function findCalls($functionName, ArgumentsWildcard $wildcard)
    {
        return array_values(
            array_filter($this->recordedCalls, function (Call $call) use ($wildcard, $functionName) {
                return $call->getMethodName() === $functionName &&
                    0 < $wildcard->scoreArguments($call->getArguments());
            })
        );
    }

    private function createUnexpectedCallException(NamespaceProphecy $prophecy, $name, array $arguments)
    {
        $argstring = implode(', ', array_map([$this->util, 'stringify'], $arguments));
        $expected = implode("\n", array_map(function (FunctionProphecy $methodProphecy) {
            return sprintf('  - %s(%s)',
                $methodProphecy->getName(),
                $methodProphecy->getArgumentsWildcard()
            );
        }, $prophecy->getProphecies()));

        return new UnexpectedCallException(
            sprintf(
                "Method call:\n".
                "  - %s(%s)\n".
                "was not expected, expected calls were:\n%s",
                $name, $argstring, $expected
            )
        );
    }
}
