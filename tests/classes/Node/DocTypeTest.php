<?php

namespace SDom\Test\Node;

use PHPUnit\Framework\TestCase;
use SDom\Node\NodeInterface;
use SDom\Test\Helper\DemoGeneratorTrait;

/**
 * Class DocTypeTest
 * 
 * @coversDefaultClass \SDom\Node\DocType
 * 
 * @package SDom\Test\Node
 */
class DocTypeTest extends TestCase
{
    use DemoGeneratorTrait;

    /**
     * @return array
     */
    public function getParents(): array
    {
        return [
            [$this->demoDocType()],
            [$this->demoCData()],
            [$this->demoComment()],
            [$this->demoElement()],
            [$this->demoVoidElement()],
            [$this->demoText()]
        ];
    }

    /**
     * Test if stringifying yields proper output.
     *
     * @covers ::__construct()
     * @covers ::__toString()
     */
    public function testConstructAndToString()
    {
        $this->assertEquals('<!DOCTYPE demo>', (string) $this->demoDocType());
    }

    /**
     * Test if parent() always returns null.
     *
     * @covers ::parent()
     */
    public function testParent()
    {
        $this->assertNull($this->demoDocType()->parent());
    }

    /**
     * Test if invoking attach() throws an exception.
     *
     * @covers ::attach()
     * @dataProvider getParents()
     * @expectedException \BadMethodCallException
     *
     * @param NodeInterface $parent
     */
    public function testAttach(NodeInterface $parent)
    {
        $this->demoDocType()->attach($parent);
    }

    /**
     * Test if invoking detach() throws an exception.
     *
     * @covers ::detach()
     * @expectedException \BadMethodCallException
     */
    public function testDetach()
    {
        $this->demoDocType()->detach();
    }
}