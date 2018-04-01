<?php

namespace SDom\Test;

use PHPUnit\Framework\TestCase;
use SDom\Node\Element;
use SDom\SelectorMatcher;
use Symfony\Component\CssSelector\Node\AttributeNode;
use Symfony\Component\CssSelector\Node\ClassNode;
use Symfony\Component\CssSelector\Node\CombinedSelectorNode;
use Symfony\Component\CssSelector\Node\ElementNode;
use Symfony\Component\CssSelector\Node\HashNode;
use Symfony\Component\CssSelector\Node\NodeInterface;
use Symfony\Component\CssSelector\Node\SelectorNode;

/**
 * Class SelectorMatcherTest
 *
 * @coversDefaultClass \SDom\SelectorMatcher
 *
 * @package SDom\Test
 */
class SelectorMatcherTest extends TestCase
{
    /**
     * @var SelectorMatcher
     */
    protected static $matcher;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$matcher = new SelectorMatcher();
    }

    /**
     * @covers ::containsWord()
     */
    public function testContainsWord()
    {
        $this->assertTrue(SelectorMatcher::containsWord('bar', 'bar'));
        $this->assertTrue(SelectorMatcher::containsWord('bar', 'foo bar'));
        $this->assertTrue(SelectorMatcher::containsWord('bar', 'bar baz'));
        $this->assertTrue(SelectorMatcher::containsWord('bar', 'foo bar baz'));

        $this->assertFalse(SelectorMatcher::containsWord('bar', 'baz'));
        $this->assertFalse(SelectorMatcher::containsWord('bar', 'foo baz'));
        $this->assertFalse(SelectorMatcher::containsWord('bar', 'baz bam'));
        $this->assertFalse(SelectorMatcher::containsWord('bar', 'foo baz bam'));
    }

    /**
     * @covers ::match()
     */
    public function testMatch()
    {
        // the match() method is just a distributor based on the type of the supplied token
        // each token is handled by a separate method, so testing here just checks if the method doesn't throw errors
        $void = new Element('a');

        self::$matcher->match(new SelectorNode(new ElementNode()), $void);
        self::$matcher->match(new ElementNode(), $void);
        self::$matcher->match(new AttributeNode(new ElementNode(), null, 'a', 'exists', null), $void);
        self::$matcher->match(new ClassNode(new ElementNode(), 'a'), $void);
        self::$matcher->match(new HashNode(new ElementNode(), 'a'), $void);
        self::$matcher->match(new CombinedSelectorNode(new ElementNode(), ' ', new ElementNode()), $void);

        $this->assertTrue(true);
    }

    /**
     * @covers ::match()
     * @expectedException \RuntimeException
     */
    public function testMatchError()
    {
        /** @var NodeInterface $mock */
        $mock = $this->getMockBuilder(NodeInterface::class)->getMock();
        self::$matcher->match($mock, new Element('a'));
    }
}