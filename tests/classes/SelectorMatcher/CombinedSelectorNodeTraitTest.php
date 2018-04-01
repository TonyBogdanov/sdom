<?php

namespace SDom\Test\SelectorMatcher;

use PHPUnit\Framework\TestCase;
use SDom\Node\Element;
use SDom\SelectorMatcher;
use SDom\Test\Helper\DemoGeneratorTrait;
use Symfony\Component\CssSelector\Node\ClassNode;
use Symfony\Component\CssSelector\Node\CombinedSelectorNode;
use Symfony\Component\CssSelector\Node\ElementNode;

/**
 * Class CombinedSelectorNodeTraitTest
 *
 * @coversDefaultClass SDom\SelectorMatcher\CombinedSelectorNodeTrait
 *
 * @package SDom\Test\SelectorMatcher
 */
class CombinedSelectorNodeTraitTest extends TestCase
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
     * @covers ::matchDescendantCombinedSelectorNode()
     *
     * @throws \ReflectionException
     */
    public function testMatchDescendantCombinedSelectorNode()
    {
        $demo = $this->demoElement(false, true);

        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchDescendantCombinedSelectorNode');
        $match->setAccessible(true);

        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), ' ', new ElementNode(null, 'a')),
            $demo->get(0)
        ), '<a/> is a descendant of <demo/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), ' ', new ElementNode(null, 'b')),
            $demo->get(1)
        ), '<b/> is a descendant of <demo/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), ' ', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is a descendant of <demo/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'b'), ' ', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is a descendant of <b/>');

        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), ' ', new ElementNode(null, 'b')),
            $demo->get(0)
        ), '<b/> does not match the sub-selector');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), ' ', new ElementNode(null, 'demo')),
            $demo
        ), '<demo/> does not have a parent');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), ' ', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is not a descendant of <a/>');
    }

    /**
     * @covers ::matchChildCombinedSelectorNode()
     *
     * @throws \ReflectionException
     */
    public function testMatchChildCombinedSelectorNode()
    {
        $demo = $this->demoElement(false, true);

        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchChildCombinedSelectorNode');
        $match->setAccessible(true);

        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), '>', new ElementNode(null, 'a')),
            $demo->get(0)
        ), '<a/> is a child of <demo/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), '>', new ElementNode(null, 'b')),
            $demo->get(1)
        ), '<b/> is a child of <demo/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'b'), '>', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is a child of <b/>');

        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), '>', new ElementNode(null, 'b')),
            $demo->get(0)
        ), '<b/> does not match the sub-selector');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), ' ', new ElementNode(null, 'demo')),
            $demo
        ), '<demo/> does not have a parent');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), '>', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is not a child of <demo/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), '>', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is not a child of <a/>');
    }

    /**
     * @covers ::matchAdjacentCombinedSelectorNode()
     *
     * @throws \ReflectionException
     */
    public function testMatchAdjacentCombinedSelectorNode()
    {
        $demo = $this->demoElement(false, true);
        $demo->insertAfter(new Element('d'));
        $demo->insertAfter($this->demoComment());
        $demo->insertAfter(new Element('e'));

        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchAdjacentCombinedSelectorNode');
        $match->setAccessible(true);

        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), '+', new ElementNode(null, 'b')),
            $demo->get(1)
        ), '<b/> is adjacent to <a/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'b'), '+', new ElementNode(null, 'd')),
            $demo->get(2)
        ), '<d/> is adjacent to <b/>');

        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), '+', new ElementNode(null, 'b')),
            $demo->get(0)
        ), '<b/> does not match the sub-selector');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'b'), '+', new ElementNode(null, 'a')),
            $demo->get(0)
        ), '<a/> is not adjacent to <b/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), '+', new ElementNode(null, 'd')),
            $demo->get(2)
        ), '<d/> is not adjacent to <a/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), '+', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is not adjacent to <a/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'b'), '+', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is not adjacent to <b/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'd'), '+', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is not adjacent to <d/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'd'), '+', new ElementNode(null, 'e')),
            $demo->get(4)
        ), '<e/> is not adjacent to an element node');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), '+', new ElementNode(null, 'demo')),
            $demo
        ), '<demo/> does not have a parent element');
    }

    /**
     * @covers ::matchGeneralSiblingCombinedSelectorNode()
     *
     * @throws \ReflectionException
     */
    public function testMatchGeneralSiblingCombinedSelectorNode()
    {
        $demo = $this->demoElement(false, true);
        $demo->insertAfter(new Element('d'));
        $demo->insertAfter($this->demoComment());
        $demo->insertAfter(new Element('e'));

        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchGeneralSiblingCombinedSelectorNode');
        $match->setAccessible(true);

        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), '~', new ElementNode(null, 'b')),
            $demo->get(1)
        ), '<b/> is a sibling to <a/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), '~', new ElementNode(null, 'd')),
            $demo->get(2)
        ), '<d/> is a sibling to <a/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'b'), '~', new ElementNode(null, 'd')),
            $demo->get(2)
        ), '<d/> is a sibling to <b/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), '~', new ElementNode(null, 'e')),
            $demo->get(4)
        ), '<e/> is a sibling to <a/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'b'), '~', new ElementNode(null, 'e')),
            $demo->get(4)
        ), '<e/> is a sibling to <b/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'd'), '~', new ElementNode(null, 'e')),
            $demo->get(4)
        ), '<e/> is a sibling to <d/>');

        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), '~', new ElementNode(null, 'b')),
            $demo->get(0)
        ), '<b/> does not match the sub-selector');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'b'), '~', new ElementNode(null, 'a')),
            $demo->get(0)
        ), '<a/> is not a sibling to <b/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'd'), '~', new ElementNode(null, 'a')),
            $demo->get(0)
        ), '<a/> is not a sibling to <d/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ClassNode(new ElementNode(null, 'a'), 'a'), '~', new ElementNode(null, 'b')),
            $demo->get(1)
        ), '<b/> is not a sibling to <a class="a"/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), '~', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is not a sibling to <a/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'b'), '~', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is not a sibling to <b/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'd'), '~', new ElementNode(null, 'c')),
            $demo->get(1)->get(0)
        ), '<c/> is not a sibling to <d/>');
        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), '+', new ElementNode(null, 'demo')),
            $demo
        ), '<demo/> does not have a parent element');
    }

    /**
     * @covers ::matchCombinedSelectorNode()
     *
     * @throws \ReflectionException
     */
    public function testMatchCombinedSelectorNode()
    {
        $demo = $this->demoElement(false, true);

        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchCombinedSelectorNode');
        $match->setAccessible(true);

        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), ' ', new ElementNode(null, 'a')),
            $demo->get(0)
        ), '<a/> is a descendant of <demo/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), '>', new ElementNode(null, 'a')),
            $demo->get(0)
        ), '<a/> is a child of <demo/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), '+', new ElementNode(null, 'b')),
            $demo->get(1)
        ), '<b/> is adjacent to <a/>');
        $this->assertTrue($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'a'), '~', new ElementNode(null, 'b')),
            $demo->get(1)
        ), '<b/> is a sibling to <a/>');

        $this->assertFalse($match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), ' ', new ElementNode(null, 'a')),
            $demo->get(1)
        ), '<b/> does not match the selector');
    }

    /**
     * @covers ::matchCombinedSelectorNode()
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Invalid combined selector combinator "^".
     *
     * @throws \ReflectionException
     */
    public function testMatchCombinedSelectorNodeInvalidCombinator()
    {
        $demo = $this->demoElement(false, true);

        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchCombinedSelectorNode');
        $match->setAccessible(true);

        $match->invoke(
            self::$matcher,
            new CombinedSelectorNode(new ElementNode(null, 'demo'), '^', new ElementNode(null, 'a')),
            $demo->get(0)
        );
    }
}