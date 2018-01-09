<?php

namespace SDom\Test\SelectorMatcher;

use PHPUnit\Framework\TestCase;
use SDom\Dom;
use SDom\SelectorMatcher\AttributeNodeTrait;
use SDom\Test\Helper\SelectorMatcherTraitMockTrait;
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
    use SelectorMatcherTraitMockTrait;

    /**
     * @covers ::matchAttributeNode()
     */
    public function testMatchAttributeNode()
    {
        $this->mockTrait(AttributeNodeTrait::class, 'matchAttributeNode');

        $void = new ElementNode();

        // exists
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', 'exists', null),
            (new Dom('<div/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', 'exists', null),
            (new Dom('<div a/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', 'exists', null),
            (new Dom('<div a="b"/>'))->get(0)));

        // =
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));

        // ~=
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a="b c"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a="a b"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '~=', 'b'),
            (new Dom('<div a="a b c"/>'))->get(0)));

        // ^=
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a="ab"/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a="abc"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '^=', 'b'),
            (new Dom('<div a="bc"/>'))->get(0)));

        // $=
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a="bc"/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a="abc"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '$=', 'b'),
            (new Dom('<div a="ab"/>'))->get(0)));

        // *=
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a="ab"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a="bc"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '*=', 'b'),
            (new Dom('<div a="abc"/>'))->get(0)));

        // |=
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="c"/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="bc"/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="a-b"/>'))->get(0)));
        $this->assertFalse($this->invoke(new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="a-b-c"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="b"/>'))->get(0)));
        $this->assertTrue($this->invoke(new AttributeNode($void, null, 'a', '|=', 'b'),
            (new Dom('<div a="b-c"/>'))->get(0)));
    }

    /**
     * @covers ::matchAttributeNode()
     * @expectedException \RuntimeException
     */
    public function testMatchAttributeNodeInvalidOperator()
    {
        $this->mockTrait(AttributeNodeTrait::class, 'matchAttributeNode');
        $this->invoke(new AttributeNode(new ElementNode(), null, 'a', '-=', 'b'), (new Dom('<div a="b"/>'))->get(0));
    }
}