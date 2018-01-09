<?php

namespace SDom\Test\SelectorMatcher;

use PHPUnit\Framework\TestCase;
use SDom\SelectorMatcher\ElementNodeTrait;
use SDom\Test\Helper\DemoGeneratorTrait;
use SDom\Test\Helper\SelectorMatcherTraitMockTrait;
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
    use SelectorMatcherTraitMockTrait;
    use DemoGeneratorTrait;

    /**
     * @covers ::matchElementNode()
     */
    public function testMatchElementNode()
    {
        $this->mockTrait(ElementNodeTrait::class, 'matchElementNode');

        $this->assertTrue($this->invoke(new ElementNode(null, 'demo'), $this->demoElement()));
        $this->assertTrue($this->invoke(new ElementNode(null, 'demo'), $this->demoElement(true)));
        $this->assertTrue($this->invoke(new ElementNode(null, 'demo'), $this->demoElement(false, true)));
        $this->assertTrue($this->invoke(new ElementNode(null, 'demo'), $this->demoElement(true, true)));

        $this->assertFalse($this->invoke(new ElementNode(null, 'other'), $this->demoElement()));
        $this->assertFalse($this->invoke(new ElementNode(null, 'other'), $this->demoElement(true)));
        $this->assertFalse($this->invoke(new ElementNode(null, 'other'), $this->demoElement(false, true)));
        $this->assertFalse($this->invoke(new ElementNode(null, 'other'), $this->demoElement(true, true)));

        $this->assertTrue($this->invoke(new ElementNode(null, 'br'), $this->demoVoidElement()));
        $this->assertTrue($this->invoke(new ElementNode(null, 'br'), $this->demoVoidElement(true)));

        $this->assertFalse($this->invoke(new ElementNode(null, 'hr'), $this->demoVoidElement()));
        $this->assertFalse($this->invoke(new ElementNode(null, 'hr'), $this->demoVoidElement(true)));

        // element node token with no tag equals an asterisk selector (should match anything)
        $this->assertTrue($this->invoke(new ElementNode(), $this->demoElement()));
        $this->assertTrue($this->invoke(new ElementNode(), $this->demoElement(true)));
        $this->assertTrue($this->invoke(new ElementNode(), $this->demoElement(false, true)));
        $this->assertTrue($this->invoke(new ElementNode(), $this->demoElement(true, true)));

        $this->assertTrue($this->invoke(new ElementNode(), $this->demoVoidElement()));
        $this->assertTrue($this->invoke(new ElementNode(), $this->demoVoidElement(true)));
    }
}