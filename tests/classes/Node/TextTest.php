<?php

namespace SDom\Test\Node;

use PHPUnit\Framework\TestCase;
use SDom\Node\Element;
use SDom\Node\NodeInterface;
use SDom\Node\Text;
use SDom\Test\Helper\DemoGeneratorTrait;

/**
 * Class TextTest
 * 
 * @coversDefaultClass \SDom\Node\Text
 * 
 * @package SDom\Test\Node
 */
class TextTest extends TestCase
{
    use DemoGeneratorTrait;

    const DEMO_RAW = 'demo <em>demo</em>';
    const DEMO_ESCAPED = 'demo &lt;em&gt;demo&lt;/em&gt;';

    /**
     * @param Element|null $parent
     * @return Text
     */
    protected function demo(Element $parent = null): Text
    {
        return new Text(self::DEMO_RAW, $parent);
    }

    /**
     * @return array
     */
    public function getValidParents(): array
    {
        return [
            [$this->demoElement()]
        ];
    }

    /**
     * @return array
     */
    public function getInvalidParents(): array
    {
        return [
            [$this->demoDocType()],
            [$this->demoCData()],
            [$this->demoComment()],
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
        $this->assertSame(self::DEMO_ESCAPED, (string) $this->demo());
    }

    /**
     * Test if native cloning throws an exception.
     *
     * @covers ::__clone()
     * @expectedException \BadMethodCallException
     */
    public function testNativeClone()
    {
        (clone $this->demoText());
    }

    /**
     * Test if cloning inherits the parent relationship to the cloned node and releases the old one.
     *
     * @covers ::clone()
     */
    public function testClone()
    {
        $child = $this->demoText();
        $preChild = $this->demoElement();
        $postChild = $this->demoElement();
        $parent = $this->demoElement()
            ->insertAfter($preChild)
            ->insertAfter($child)
            ->insertAfter($postChild);

        $clone = $child->clone();

        $this->assertInstanceOf(get_class($child), $clone);
        $this->assertNotSame($child, $clone);
        $this->assertSame((string) $child, (string) $clone);

        $this->assertTrue($parent->isChild($child));
        $this->assertFalse($parent->isChild($clone));
        $this->assertSame($parent, $child->parent());
        $this->assertNull($clone->parent());

        // make sure original tree is kept
        $this->assertCount(3, $parent);
        $this->assertSame($preChild, $parent->getIterator()[0]);
        $this->assertSame($child, $parent->getIterator()[1]);
        $this->assertSame($postChild, $parent->getIterator()[2]);
    }

    /**
     * Test if parent() returns proper result.
     *
     * @covers ::parent()
     */
    public function testParent()
    {
        $parent = $this->demoElement();
        $demo = $this->demoText();

        $this->assertNull($demo->parent());

        $parent->insertAfter($demo);
        $this->assertSame($parent, $demo->parent());
    }

    /**
     * Test if attaching valid node as parent succeeds.
     *
     * @covers ::attach()
     * @dataProvider getValidParents()
     *
     * @param NodeInterface $parent
     */
    public function testAttach(NodeInterface $parent)
    {
        $demo = $this->demo();

        $this->assertNull($demo->parent());
        $demo->attach($parent);

        $this->assertSame($parent, $demo->parent());
    }

    /**
     * Test if attaching invalid node as parent throws an error.
     *
     * @covers ::attach()
     * @dataProvider getInvalidParents()
     * @expectedException \InvalidArgumentException
     *
     * @param NodeInterface $parent
     */
    public function testAttachError(NodeInterface $parent)
    {
        $demo = $this->demo();

        $this->assertNull($demo->parent());
        $demo->attach($parent);
    }

    /**
     * Test if attaching a node to a new parent detaches it from the first parent.
     *
     * @covers ::attach()
     */
    public function testReAttach()
    {
        $parent1 = $this->demoElement();
        $parent2 = $this->demoElement();
        $demo = $this->demoText();

        $parent1->insertAfter($demo);
        $parent2->insertAfter($demo);

        $this->assertFalse($parent1->isChild($demo));
        $this->assertTrue($parent2->isChild($demo));
        $this->assertSame($parent2, $demo->parent());
    }

    /**
     * Test if detaching parent succeeds.
     *
     * @covers ::detach()
     */
    public function testDetach()
    {
        $parent = $this->demoElement();
        $demo = $this->demoText();

        $parent->insertAfter($demo);
        $demo->detach();

        $this->assertNull($demo->parent());
    }
}