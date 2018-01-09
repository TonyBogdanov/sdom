<?php

namespace SDom\Test\SelectorMatcher;

use PHPUnit\Framework\TestCase;
use SDom\Dom;
use SDom\SelectorMatcher\HashNodeTrait;
use SDom\Test\Helper\SelectorMatcherTraitMockTrait;
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
    use SelectorMatcherTraitMockTrait;

    /**
     * @covers ::matchHashNode()
     */
    public function testMatchHashNode()
    {
        $this->mockTrait(HashNodeTrait::class, 'matchHashNode');

        $void = new ElementNode();

        $this->assertFalse($this->invoke(new HashNode($void, 'demo'), (new Dom('<div/>'))->get(0)));
        $this->assertFalse($this->invoke(new HashNode($void, 'demo'), (new Dom('<div id="demo2"/>'))->get(0)));
        $this->assertTrue($this->invoke(new HashNode($void, 'demo'), (new Dom('<div id="demo"/>'))->get(0)));
    }
}