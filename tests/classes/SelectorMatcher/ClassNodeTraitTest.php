<?php

namespace SDom\Test\SelectorMatcher;

use PHPUnit\Framework\TestCase;
use SDom\Dom;
use SDom\SelectorMatcher\ClassNodeTrait;
use SDom\Test\Helper\SelectorMatcherTraitMockTrait;
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
    use SelectorMatcherTraitMockTrait;

    /**
     * @covers ::matchClassNode()
     */
    public function testMatchClassNode()
    {
        $this->mockTrait(ClassNodeTrait::class, 'matchClassNode');

        $void = new ElementNode();

        $this->assertFalse($this->invoke(new ClassNode($void, 'b'), (new Dom('<div/>'))->get(0)));
        $this->assertFalse($this->invoke(new ClassNode($void, 'b'), (new Dom('<div class="a"/>'))->get(0)));
        $this->assertFalse($this->invoke(new ClassNode($void, 'b'), (new Dom('<div class="a c"/>'))->get(0)));
        $this->assertFalse($this->invoke(new ClassNode($void, 'b'), (new Dom('<div class="abc"/>'))->get(0)));
        $this->assertFalse($this->invoke(new ClassNode($void, 'b'), (new Dom('<div class="bc"/>'))->get(0)));
        $this->assertFalse($this->invoke(new ClassNode($void, 'b'), (new Dom('<div class="cb"/>'))->get(0)));
        $this->assertTrue($this->invoke(new ClassNode($void, 'b'), (new Dom('<div class="b"/>'))->get(0)));
        $this->assertTrue($this->invoke(new ClassNode($void, 'b'), (new Dom('<div class="a b"/>'))->get(0)));
        $this->assertTrue($this->invoke(new ClassNode($void, 'b'), (new Dom('<div class="b c"/>'))->get(0)));
        $this->assertTrue($this->invoke(new ClassNode($void, 'b'), (new Dom('<div class="a b c"/>'))->get(0)));
    }
}