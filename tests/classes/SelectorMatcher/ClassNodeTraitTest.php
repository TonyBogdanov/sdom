<?php

namespace SDom\Test\SelectorMatcher;

use PHPUnit\Framework\TestCase;
use SDom\Dom;
use SDom\SelectorMatcher;
use Symfony\Component\CssSelector\Node\ClassNode;
use Symfony\Component\CssSelector\Node\ElementNode;

/**
 * Class ClassNodeTraitTest
 *
 * @coversDefaultClass SDom\SelectorMatcher\ClassNodeTrait
 *
 * @package SDom\Test\SelectorMatcher
 */
class ClassNodeTraitTest extends TestCase
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
     * @covers ::matchClassNode()
     * 
     * @throws \ReflectionException
     */
    public function testMatchClassNode()
    {
        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchClassNode');
        $match->setAccessible(true);

        $void = new ElementNode();

        $this->assertFalse($match->invoke(self::$matcher, new ClassNode($void, 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new ClassNode($void, 'b'),
            (new Dom('<div class="a"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new ClassNode($void, 'b'),
            (new Dom('<div class="a c"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new ClassNode($void, 'b'),
            (new Dom('<div class="abc"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new ClassNode($void, 'b'),
            (new Dom('<div class="bc"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new ClassNode($void, 'b'),
            (new Dom('<div class="cb"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new ClassNode($void, 'b'),
            (new Dom('<div class="b"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new ClassNode($void, 'b'),
            (new Dom('<div class="a b"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new ClassNode($void, 'b'),
            (new Dom('<div class="b c"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new ClassNode($void, 'b'),
            (new Dom('<div class="a b c"/>'))->get(0)));
    }
}