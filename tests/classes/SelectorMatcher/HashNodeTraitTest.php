<?php

namespace SDom\Test\SelectorMatcher;

use PHPUnit\Framework\TestCase;
use SDom\Dom;
use SDom\SelectorMatcher;
use Symfony\Component\CssSelector\Node\ElementNode;
use Symfony\Component\CssSelector\Node\HashNode;

/**
 * Class HashNodeTraitTest
 *
 * @coversDefaultClass SDom\SelectorMatcher\HashNodeTrait
 *
 * @package SDom\Test\SelectorMatcher
 */
class HashNodeTraitTest extends TestCase
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
     * @covers ::matchHashNode()
     * 
     * @throws \ReflectionException
     */
    public function testMatchHashNode()
    {
        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchHashNode');
        $match->setAccessible(true);

        $void = new ElementNode();

        $this->assertFalse($match->invoke(self::$matcher, new HashNode($void, 'demo'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new HashNode($void, 'demo'),
            (new Dom('<div id="demo2"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new HashNode($void, 'demo'),
            (new Dom('<div id="demo"/>'))->get(0)));
    }
}