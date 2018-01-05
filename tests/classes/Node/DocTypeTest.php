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
        $this->assertSame('<!DOCTYPE demo>', (string) $this->demoDocType());
    }

    /**
     * Test if native cloning throws an exception.
     *
     * @covers ::__clone()
     * @expectedException \BadMethodCallException
     */
    public function testNativeClone()
    {
        (clone $this->demoDocType());
    }

    /**
     * Test if cloning succeeds.
     * Parent relationships are not applicable.
     *
     * @covers ::clone()
     */
    public function testClone()
    {
        $child = $this->demoDocType();
        $clone = $child->clone();

        $this->assertInstanceOf(get_class($child), $clone);
        $this->assertNotSame($child, $clone);
        $this->assertSame((string) $child, (string) $clone);
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