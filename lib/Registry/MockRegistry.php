<?php

namespace Kcs\FunctionMock\Registry;

use Kcs\FunctionMock\FunctionProphecy;
use Kcs\FunctionMock\Generator\Generator;
use Kcs\FunctionMock\NamespaceProphecy;

class MockRegistry
{
    /**
     * @var NamespaceProphecy[]
     */
    protected $mocks = [];

    /**
     * @var MockRegistry
     */
    private static $instance = null;

    /**
     * Get singleton instance of the registry
     *
     * @return MockRegistry
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Has mock registered for this function?
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->mocks[$name]);
    }

    /**
     * Register a new function prophecy into the registry
     *
     * @param FunctionProphecy $prophecy
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function register(FunctionProphecy $prophecy)
    {
        $name = $prophecy->getFQName();

        if ($this->has($name)) {
            throw new \Exception();
        }

        if (! function_exists($name)) {
            Generator::generate($prophecy);
        }

        $this->mocks[$name] = $prophecy->getNamespace();

        return $this;
    }

    /**
     * Call the registered function mock
     *
     * @param string $name The fully-qualified function name
     * @param string $uqfn The unqualified function name
     * @param array $args
     *
     * @return mixed
     * @throws \Exception
     *
     * @internal
     */
    public function call($name, $uqfn, array $args)
    {
        if (! $this->has($name)) {
            throw new \Exception();
        }

        return $this->mocks[$name]->call($uqfn, $args);
    }

    /**
     * Unregister all the mocks
     */
    public function unregisterAll()
    {
        $this->mocks = [];
    }
}
