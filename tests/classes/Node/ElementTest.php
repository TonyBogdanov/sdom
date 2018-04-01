<?php

namespace SDom\Test\Node;

use PHPUnit\Framework\TestCase;
use SDom\Node\Element;
use SDom\Node\NodeInterface;
use SDom\Node\Text;
use SDom\Test\Helper\DemoGeneratorTrait;

/**
 * Class ElementTest
 * 
 * @coversDefaultClass \SDom\Node\Element
 * 
 * @package SDom\Test\Node
 */
class ElementTest extends TestCase
{
    use DemoGeneratorTrait;

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
     * Test if stringifying yields proper output (regular and void elements).
     *
     * @covers ::__construct()
     * @covers ::__toString()
     */
    public function testConstructAndToString()
    {
        $this->assertSame(
            (string) $this->demoElement(),
            (string) $this->demoElement()
        );
        $this->assertSame(
            (string) $this->demoVoidElement(),
            (string) $this->demoVoidElement()
        );

        // with attributes
        $this->assertSame(
            (string) $this->demoElement(true),
            (string) $this->demoElement()->setAttribute('a', 'b')->setAttribute('c', 'd')->setAttribute('e')
        );
        $this->assertSame(
            (string) $this->demoVoidElement(true),
            (string) $this->demoVoidElement()->setAttribute('a', 'b')->setAttribute('c', 'd')->setAttribute('e')
        );

        // with children
        $this->assertSame(
            (string) $this->demoElement(false, true),
            (string) $this->demoElement()->insertAfter(new Element('a'))->insertAfter((new Element('b'))
                ->insertAfter(new Element('c')))
        );

        // with attributes & children
        $this->assertSame(
            (string) $this->demoElement(true, true),
            (string) $this->demoElement()
                ->setAttribute('a', 'b')
                ->setAttribute('c', 'd')
                ->setAttribute('e')
                ->insertAfter(new Element('a'))
                ->insertAfter((new Element('b'))->insertAfter(new Element('c')))
        );
    }

    /**
     * Test if native cloning throws an exception.
     *
     * @covers ::__clone()
     * @expectedException \BadMethodCallException
     */
    public function testNativeClone()
    {
        (clone $this->demoElement());
    }

    /**
     * Test if cloning inherits the parent relationship to the cloned node and releases the old one.
     *
     * @covers ::clone()
     */
    public function testClone()
    {
        $child = $this->demoElement(true);
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
     * Test that child nodes are recursively cloned too (not just immediate children).
     *
     * @covers ::clone()
     */
    public function testDeepClone()
    {
        $elements = [
            new Element('parent'),
            new Element('child1'),
            new Element('child1a'),
            new Element('child1b'),
            new Element('child2'),
            new Element('child2a'),
            new Element('child2b'),
        ];

        $elements[0]->insertAfter(
            $elements[1]->insertAfter($elements[2])
                ->insertAfter($elements[3])
        )->insertAfter(
            $elements[4]->insertAfter($elements[5])
                ->insertAfter($elements[6])
        );

        // flatten the new (supposed) tree
        $cloned = [$elements[0]->clone()];
        $cloned[1] = $cloned[0]->getIterator()[0];
        $cloned[2] = $cloned[1]->getIterator()[0];
        $cloned[3] = $cloned[1]->getIterator()[1];
        $cloned[4] = $cloned[0]->getIterator()[1];
        $cloned[5] = $cloned[4]->getIterator()[0];
        $cloned[6] = $cloned[4]->getIterator()[1];

        for ($i = 0; $i < 7; $i++) {
            $this->assertInstanceOf(get_class($elements[$i]), $cloned[$i]);
            $this->assertNotSame($elements[$i], $cloned[$i]);
            $this->assertSame((string) $elements[$i], (string) $cloned[$i]);
        }

        // test if tree is both kept and inherited
        /** @var Element[] $batch */
        foreach ([$elements, $cloned] as $batch) {
            $this->assertNull($batch[0]->parent());
            $this->assertSame($batch[0], $batch[1]->parent());
            $this->assertSame($batch[1], $batch[2]->parent());
            $this->assertSame($batch[1], $batch[3]->parent());
            $this->assertSame($batch[4], $batch[5]->parent());
            $this->assertSame($batch[4], $batch[6]->parent());
            $this->assertTrue($batch[0]->isChild($batch[1]));
            $this->assertTrue($batch[1]->isChild($batch[2]));
            $this->assertTrue($batch[1]->isChild($batch[3]));
            $this->assertTrue($batch[4]->isChild($batch[5]));
            $this->assertTrue($batch[4]->isChild($batch[6]));
        }
    }

    /**
     * @covers ::getTag()
     */
    public function testGetTag()
    {
        $demo = $this->demoElement();

        $this->assertSame('demo', $demo->getTag());
    }

    /**
     * @covers ::hasAttribute()
     */
    public function testHasAttribute()
    {
        $demo = $this->demoElement();

        $this->assertFalse($demo->hasAttribute('demo'));

        $demo->setAttribute('demo', 'demo');
        $demo->setAttribute('noval');

        $this->assertTrue($demo->hasAttribute('demo'));
        $this->assertTrue($demo->hasAttribute('noval'));
    }

    /**
     * @covers ::setAttribute()
     * @covers ::getAttribute()
     */
    public function testSetGetAttribute()
    {
        $demo = $this->demoElement();

        $this->assertNull($demo->getAttribute('demo'));

        $demo->setAttribute('demo', 'demo');
        $this->assertSame('demo', $demo->getAttribute('demo'));

        $demo->setAttribute('demo', 'changed');
        $this->assertSame('changed', $demo->getAttribute('demo'));
    }

    /**
     * @covers ::removeAttribute()
     */
    public function testRemoveAttribute()
    {
        $demo = $this->demoElement();

        $demo->setAttribute('demo', 'demo');
        $demo->removeAttribute('demo');

        $this->assertFalse($demo->hasAttribute('demo'));
        $this->assertNull($demo->getAttribute('demo'));
    }

    /**
     * Test if parent() returns proper result.
     *
     * @covers ::parent()
     */
    public function testParent()
    {
        $parent = $this->demoElement();
        $demo = $this->demoElement();

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
        $demo = $this->demoElement();

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
        $demo = $this->demoElement();

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
        $demo = $this->demoElement();

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
        $demo = $this->demoElement();

        $parent->insertAfter($demo);
        $demo->detach();

        $this->assertNull($demo->parent());
    }

    /**
     * Test \IteratorAggregate
     *
     * @covers ::getIterator()
     */
    public function testGetIterator()
    {
        $demo = $this->demoElement();
        $child1 = $this->demoComment();
        $child2 = $this->demoCData();
        $child3 = $this->demoText();

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertSame([], $children);

        $demo->insertAfter($child1)->insertAfter($child2)->insertAfter($child3);

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertSame([$child1, $child2, $child3], $children);

        // test depth
        $deep = $this->demoElement()->insertAfter($this->demoComment());
        $demo->insertAfter($deep);

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertSame([$child1, $child2, $child3, $deep], $children);
    }

    /**
     * Test \Countable.
     *
     * @covers ::count()
     */
    public function testCount()
    {
        $demo = $this->demoElement();

        $this->assertCount(0, $demo);

        $demo->insertAfter($this->demoCData());
        $this->assertCount(1, $demo);

        $demo->insertAfter($this->demoComment());
        $this->assertCount(2, $demo);

        // test depth
        $demo->insertAfter($this->demoElement()->insertAfter($this->demoComment()));
        $this->assertCount(3, $demo);
    }

    /**
     * Test if insertAfter() properly appends nodes & updates parent references for element nodes.
     *
     * @covers ::insertAfter()
     */
    public function testInsertAfter()
    {
        $demo = $this->demoElement();
        $child1 = $this->demoComment();
        $child2 = $this->demoCData();
        $child3 = $this->demoElement();
        $deepChild = $this->demoElement();
        $deeperChild = $this->demoText();

        $demo->insertAfter($child1)
            ->insertAfter($child2)
            ->insertAfter($child3->insertAfter($deepChild->insertAfter($deeperChild)));

        $this->assertCount(3, $demo);
        $this->assertCount(1, $child3);
        $this->assertCount(1, $deepChild);
        $this->assertSame($child1, $demo->getIterator()[0]);
        $this->assertSame($child2, $demo->getIterator()[1]);
        $this->assertSame($child3, $demo->getIterator()[2]);
        $this->assertSame($deepChild, $child3->getIterator()[0]);
        $this->assertSame($deeperChild, $deepChild->getIterator()[0]);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($child2));
        $this->assertTrue($demo->isChild($child3));
        $this->assertTrue($child3->isChild($deepChild));
        $this->assertTrue($deepChild->isChild($deeperChild));
        $this->assertSame($demo, $child1->parent());
        $this->assertSame($demo, $child2->parent());
        $this->assertSame($demo, $child3->parent());
        $this->assertSame($child3, $deepChild->parent());
        $this->assertSame($deepChild, $deeperChild->parent());

        // test anchored
        $demo->clear()
            ->insertAfter($child1)
            ->insertAfter($child2)
            ->insertAfter($child3, $child1);

        $this->assertCount(3, $demo);
        $this->assertCount(1, $child3);
        $this->assertCount(1, $deepChild);
        $this->assertSame($child1, $demo->getIterator()[0]);
        $this->assertSame($child3, $demo->getIterator()[1]);
        $this->assertSame($child2, $demo->getIterator()[2]);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($child2));
        $this->assertTrue($demo->isChild($child3));
        $this->assertTrue($child3->isChild($deepChild));
        $this->assertTrue($deepChild->isChild($deeperChild));
        $this->assertSame($demo, $child1->parent());
        $this->assertSame($demo, $child2->parent());
        $this->assertSame($demo, $child3->parent());
        $this->assertSame($child3, $deepChild->parent());
        $this->assertSame($deepChild, $deeperChild->parent());

        // test depth
        $deep = $this->demoElement()
            ->insertAfter($child2);

        $demo->clear()
            ->insertAfter($child1)
            ->insertAfter($deep);

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertSame([$child1, $deep], $children);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($deep));
        $this->assertFalse($demo->isChild($child2));
        $this->assertTrue($child3->isChild($deepChild));
        $this->assertTrue($deepChild->isChild($deeperChild));
        $this->assertSame($demo, $child1->parent());
        $this->assertSame($demo, $deep->parent());
        $this->assertSame($deep, $child2->parent());
        $this->assertSame($child3, $deepChild->parent());
        $this->assertSame($deepChild, $deeperChild->parent());
    }

    /**
     * Test if calling insertAfter() with an anchor which isn't already a child of the node throws an exception.
     *
     * @covers ::insertAfter()
     * @expectedException \InvalidArgumentException
     */
    public function testInsertAfterError()
    {
        $this->demoElement()->insertAfter($this->demoComment(), $this->demoText());
    }

    /**
     * Test if insertBefore() properly prepends nodes & updates parent references for element nodes.
     *
     * @covers ::insertBefore()
     */
    public function testInsertBefore()
    {
        $demo = $this->demoElement();
        $child1 = $this->demoComment();
        $child2 = $this->demoCData();
        $child3 = $this->demoElement();
        $deepChild = $this->demoElement();
        $deeperChild = $this->demoText();

        $demo->insertBefore($child1)
            ->insertBefore($child2)
            ->insertBefore($child3->insertAfter($deepChild->insertAfter($deeperChild)));

        $this->assertCount(3, $demo);
        $this->assertCount(1, $child3);
        $this->assertCount(1, $deepChild);
        $this->assertSame($child3, $demo->getIterator()[0]);
        $this->assertSame($child2, $demo->getIterator()[1]);
        $this->assertSame($child1, $demo->getIterator()[2]);
        $this->assertSame($deepChild, $child3->getIterator()[0]);
        $this->assertSame($deeperChild, $deepChild->getIterator()[0]);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($child2));
        $this->assertTrue($demo->isChild($child3));
        $this->assertTrue($child3->isChild($deepChild));
        $this->assertTrue($deepChild->isChild($deeperChild));
        $this->assertSame($demo, $child1->parent());
        $this->assertSame($demo, $child2->parent());
        $this->assertSame($demo, $child3->parent());
        $this->assertSame($child3, $deepChild->parent());
        $this->assertSame($deepChild, $deeperChild->parent());

        // test anchored
        $demo->clear()
            ->insertBefore($child1)
            ->insertBefore($child2)
            ->insertBefore($child3, $child1);

        $this->assertCount(3, $demo);
        $this->assertCount(1, $child3);
        $this->assertCount(1, $deepChild);
        $this->assertSame($child2, $demo->getIterator()[0]);
        $this->assertSame($child3, $demo->getIterator()[1]);
        $this->assertSame($child1, $demo->getIterator()[2]);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($child2));
        $this->assertTrue($demo->isChild($child3));
        $this->assertTrue($child3->isChild($deepChild));
        $this->assertTrue($deepChild->isChild($deeperChild));
        $this->assertSame($demo, $child1->parent());
        $this->assertSame($demo, $child2->parent());
        $this->assertSame($demo, $child3->parent());
        $this->assertSame($child3, $deepChild->parent());
        $this->assertSame($deepChild, $deeperChild->parent());

        // test depth
        $deep = $this->demoElement()
            ->insertBefore($child2);

        $demo->clear()
            ->insertBefore($child1)
            ->insertBefore($deep);

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertSame([$deep, $child1], $children);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($deep));
        $this->assertFalse($demo->isChild($child2));
        $this->assertTrue($child3->isChild($deepChild));
        $this->assertTrue($deepChild->isChild($deeperChild));
        $this->assertSame($demo, $child1->parent());
        $this->assertSame($demo, $deep->parent());
        $this->assertSame($deep, $child2->parent());
        $this->assertSame($child3, $deepChild->parent());
        $this->assertSame($deepChild, $deeperChild->parent());
    }

    /**
     * Test if calling insertBefore() with an anchor which isn't already a child of the node throws an exception.
     *
     * @covers ::insertBefore()
     * @expectedException \InvalidArgumentException
     */
    public function testInsertBeforeError()
    {
        $this->demoElement()->insertBefore($this->demoComment(), $this->demoText());
    }

    /**
     * Test if isChild() returns TRUE for immediate children.
     *
     * @covers ::isChild()
     */
    public function testIsChild()
    {
        $child1 = $this->demoComment();
        $child2 = $this->demoElement();
        $deepChild = $this->demoComment();
        $notChild = $this->demoCData();

        $parent = $this->demoElement();

        $this->assertFalse($parent->isChild($child1));
        $this->assertFalse($parent->isChild($child2));
        $this->assertFalse($parent->isChild($deepChild));
        $this->assertFalse($parent->isChild($notChild));

        $child2->insertAfter($deepChild);
        $parent->insertAfter($child1);
        $parent->insertAfter($child2);

        $this->assertTrue($parent->isChild($child1));
        $this->assertTrue($parent->isChild($child2));
        $this->assertFalse($parent->isChild($deepChild));
        $this->assertFalse($parent->isChild($notChild));

        $this->assertSame($parent, $child1->parent());
        $this->assertSame($parent, $child2->parent());
        $this->assertSame($child2, $deepChild->parent());
        $this->assertNull($notChild->parent());
    }

    /**
     * Test if get() returns proper immediate child.
     *
     * @covers ::get()
     */
    public function testGet()
    {
        $immediateChild1 = null;
        $immediateChild2 = null;

        $parent = $this->demoElement(false, true, $immediateChild1, $immediateChild2);

        $this->assertSame($immediateChild1, $parent->get(0));
        $this->assertSame($immediateChild2, $parent->get(1));
    }

    /**
     * Test if get() throws an \OutOfBoundsException for index below 0.
     *
     * @covers ::get()
     * @expectedException \OutOfBoundsException
     */
    public function testGetOutOfBoundsBelow()
    {
        $this->demoElement(false, true)->get(-1);
    }

    /**
     * Test if get() throws an \OutOfBoundsException for index above the number of children.
     *
     * @covers ::get()
     * @expectedException \OutOfBoundsException
     */
    public function testGetOutOfBoundsAbove()
    {
        $this->demoElement(false, true)->get(2);
    }

    /**
     * Test if index() returns proper immediate child index.
     *
     * @covers ::index()
     */
    public function testIndex()
    {
        $immediateChild1 = null;
        $immediateChild2 = null;

        $parent = $this->demoElement(false, true, $immediateChild1, $immediateChild2);

        $this->assertSame(0, $parent->index($immediateChild1));
        $this->assertSame(1, $parent->index($immediateChild2));
    }

    /**
     * Test if index() throws \InvalidArgumentException if the specified node is not an immediate child.
     *
     * @covers ::index()
     * @expectedException \InvalidArgumentException
     */
    public function testIndexInvalid()
    {
        $null = null;
        $deepChild = null;

        $this->demoElement(false, true, $null, $null, $deepChild)->index($deepChild);
    }

    /**
     * Test if calling removeChild() removes the specified immediate children.
     *
     * @covers ::removeChild()
     */
    public function testRemoveChild()
    {
        $deepDemo = $this->demoComment();
        $demo1 = $this->demoComment();
        $demo2 = $this->demoCData();
        $demo3 = $this->demoElement()->insertAfter($deepDemo);

        $demo = $this->demoElement()
            ->insertAfter($demo1)
            ->insertAfter($demo2)
            ->insertAfter($demo3)
            ->removeChild($demo1)
            ->removeChild($demo3);

        $this->assertCount(1, $demo);
        $this->assertFalse($demo->isChild($demo1));
        $this->assertTrue($demo->isChild($demo2));
        $this->assertFalse($demo->isChild($demo3));
        $this->assertFalse($demo->isChild($deepDemo));
        $this->assertTrue($demo3->isChild($deepDemo)); // test that removeChild() didn't mess depth relations
    }

    /**
     * Test if calling removeChild() with node which isn't already a child of the node throws an exception.
     *
     * @covers ::removeChild()
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveChildError()
    {
        $this->demoElement()->removeChild($this->demoComment());
    }

    /**
     * Test if clear() removes all immediate children.
     *
     * @covers ::clear()
     */
    public function testClear()
    {
        $deepDemo = $this->demoComment();
        $demo1 = $this->demoComment();
        $demo2 = $this->demoElement()->insertAfter($deepDemo);

        $demo = $this->demoElement()->insertAfter($demo1)->insertAfter($demo2)->clear();

        $this->assertCount(0, $demo);
        $this->assertFalse($demo->isChild($demo1));
        $this->assertFalse($demo->isChild($demo2));
        $this->assertFalse($demo->isChild($deepDemo));
        $this->assertTrue($demo2->isChild($deepDemo)); // test that clear() didn't mess depth relations
    }

    /**
     * Test if isVoid() returns proper result.
     *
     * @covers ::isVoid()
     */
    public function testIsVoid()
    {
        $this->assertFalse($this->demoElement()->isVoid());
        $this->assertTrue($this->demoVoidElement()->isVoid());
    }
}