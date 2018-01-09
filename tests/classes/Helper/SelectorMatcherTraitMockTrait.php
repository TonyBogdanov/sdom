<?php

namespace SDom\Test\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Trait SelectorMatcherTraitMockTrait
 * @package SDom\Test\Helper
 */
trait SelectorMatcherTraitMockTrait
{
    /**
     * @var MockObject
     */
    protected $mockedTrait;

    /**
     * @var \ReflectionMethod
     */
    protected $mockedMethod;

    /**
     * @param string $trait
     * @param string $method
     * @return $this
     */
    protected function mockTrait(string $trait, string $method)
    {
        /** @var TestCase|SelectorMatcherTraitMockTrait $this */

        // create a mock object for the specified trait
        $this->mockedTrait = $this->getMockBuilder($trait)
            ->setMethods(['match'])
            ->getMockForTrait();

        // mock the generic match() method from SelectorMatcher to always return TRUE
        // this will effectively bypass dependencies
        $this->mockedTrait->method('match')
            ->willReturn(true);

        // save a method reflection to allow invoking trait's protected method
        $reflection = new \ReflectionObject($this->mockedTrait);
        $this->mockedMethod = $reflection->getMethod($method);
        $this->mockedMethod->setAccessible(true);

        return $this;
    }

    /**
     * @param array ...$arguments
     * @return mixed
     */
    protected function invoke(...$arguments)
    {
        return $this->mockedMethod->invokeArgs($this->mockedTrait, $arguments);
    }
}