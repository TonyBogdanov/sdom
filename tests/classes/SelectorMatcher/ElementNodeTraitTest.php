<?php

namespace SDom\Test\SelectorMatcher;

use PHPUnit\Framework\TestCase;
use SDom\SelectorMatcher;
use SDom\Test\Helper\DemoGeneratorTrait;
use Symfony\Component\CssSelector\Node\ElementNode;

/**
 * Class ElementNodeTraitTest
 *
 * @coversDefaultClass SDom\SelectorMatcher\ElementNodeTrait
 *
 * @package SDom\Test\SelectorMatcher
 */
class ElementNodeTraitTest extends TestCase
{
    use DemoGeneratorTrait;
    
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
     * @covers ::matchElementNode()
     *
     * @throws \ReflectionException
     */
    public function testMatchElementNode()
    {
        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchElementNode');
        $match->setAccessible(true);

        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(null, 'demo'),
            $this->demoElement()));
        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(null, 'demo'),
            $this->demoElement(true)));
        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(null, 'demo'),
            $this->demoElement(false, true)));
        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(null, 'demo'),
            $this->demoElement(true, true)));

        $this->assertFalse($match->invoke(self::$matcher, new ElementNode(null, 'other'),
            $this->demoElement()));
        $this->assertFalse($match->invoke(self::$matcher, new ElementNode(null, 'other'),
            $this->demoElement(true)));
        $this->assertFalse($match->invoke(self::$matcher, new ElementNode(null, 'other'),
            $this->demoElement(false, true)));
        $this->assertFalse($match->invoke(self::$matcher, new ElementNode(null, 'other'),
            $this->demoElement(true, true)));

        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(null, 'br'),
            $this->demoVoidElement()));
        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(null, 'br'),
            $this->demoVoidElement(true)));

        $this->assertFalse($match->invoke(self::$matcher, new ElementNode(null, 'hr'),
            $this->demoVoidElement()));
        $this->assertFalse($match->invoke(self::$matcher, new ElementNode(null, 'hr'),
            $this->demoVoidElement(true)));

        // element node token with no tag equals an asterisk selector (should match anything)
        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(),
            $this->demoElement()));
        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(),
            $this->demoElement(true)));
        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(),
            $this->demoElement(false, true)));
        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(),
            $this->demoElement(true, true)));

        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(),
            $this->demoVoidElement()));
        $this->assertTrue($match->invoke(self::$matcher, new ElementNode(),
            $this->demoVoidElement(true)));
    }
}