<?php

namespace Kcs\FunctionMock\Generator;

use Kcs\FunctionMock\FunctionProphecy;

/**
 * Generate and inject a function
 *
 * @internal
 */
class Generator
{
    private static $template = <<<EOF
namespace {namespace} {
    function {function_name}()
    {
        \$registry = \Kcs\FunctionMock\Registry\MockRegistry::getInstance();
        \$fqfn = '{namespace}\{function_name}';
        if (!\$registry->has(\$fqfn)) {
            return call_user_func_array('{function_name}', func_get_args());
        }

        return \$registry->call(\$fqfn, '{function_name}', func_get_args());
    }
}
EOF;

    public static function generate(FunctionProphecy $prophecy)
    {
        $body = strtr(self::$template, [
            '{namespace}' => $prophecy->getNamespace()->getName(),
            '{function_name}' => $prophecy->getName(),
        ]);

        eval('?><?php '.$body);
    }
}
