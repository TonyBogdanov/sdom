<?php

namespace SDom\Test\SelectorMatcher;

use PHPUnit\Framework\TestCase;
use SDom\Dom;
use SDom\SelectorMatcher;
use Symfony\Component\CssSelector\Node\AttributeNode;
use Symfony\Component\CssSelector\Node\ElementNode;

/**
 * Class AttributeNodeTraitTest
 *
 * @coversDefaultClass SDom\SelectorMatcher\AttributeNodeTrait
 *
 * @package SDom\Test\SelectorMatcher
 */
class AttributeNodeTraitTest extends TestCase
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
     * @covers ::matchAttributeNode()
     *
     * @throws \ReflectionException
     */
    public function testMatchAttributeNode()
    {
        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchAttributeNode');
        $match->setAccessible(true);

        $void = new ElementNode();

        // exists
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', 'exists', null),
            (new Dom('<div/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', 'exists', null),
            (new Dom('<div a/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', 'exists', null),
            (new Dom('<div a="b"/>'))->get(0)));

        // =
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));

        // ~=
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a="b c"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a="a b"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a="a b c"/>'))->get(0)));

        // ^=
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a="ab"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a="abc"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a="bc"/>'))->get(0)));

        // $=
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a="bc"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a="abc"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a="ab"/>'))->get(0)));

        // *=
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a="ab"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a="bc"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a="abc"/>'))->get(0)));

        // |=
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="bc"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="a-b"/>'))->get(0)));
        $this->assertFalse($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="a-b-c"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));
        $this->assertTrue($match->invoke(self::$matcher, new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="b-c"/>'))->get(0)));
    }

    /**
     * @covers ::matchAttributeNode()
     * @expectedException \RuntimeException
     * 
     * @throws \ReflectionException
     */
    public function testMatchAttributeNodeInvalidOperator()
    {
        $match = (new \ReflectionClass(SelectorMatcher::class))->getMethod('matchAttributeNode');
        $match->setAccessible(true);

        $match->invoke(self::$matcher, new AttributeNode(new ElementNode(), null, 'a', '-=', 'b'),
            (new Dom('<div a="b"/>'))->get(0));
    }
}