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
        $this->assertEquals('<demo></demo>', (string) $this->demoElement());
        $this->assertEquals('<br/>', (string) $this->demoVoidElement());

        // with attributes
        $this->assertEquals(
            '<demo a="b" c="d"></demo>',
            (string) $this->demoElement()->setAttribute('a', 'b')->setAttribute('c', 'd')
        );
        $this->assertEquals(
            '<br a="b" c="d"/>',
            (string) $this->demoVoidElement()->setAttribute('a', 'b')->setAttribute('c', 'd')
        );

        // with children
        $this->assertEquals(
            '<demo><a></a><b></b></demo>',
            (string) $this->demoElement()->insertAfter(new Element('a'))->insertAfter(new Element('b'))
        );

        // with attributes & children
        $this->assertEquals(
            '<demo a="b" c="d"><a></a><b></b></demo>',
            (string) $this->demoElement()
                ->setAttribute('a', 'b')
                ->setAttribute('c', 'd')
                ->insertAfter(new Element('a'))
                ->insertAfter(new Element('b'))
        );
    }

    /**
     * @covers ::hasAttribute()
     */
    public function testHasAttribute()
    {
        $demo = $this->demoElement();

        $this->assertFalse($demo->hasAttribute('demo'));

        $demo->setAttribute('demo', 'demo');
        $this->assertTrue($demo->hasAttribute('demo'));
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
        $this->assertEquals('demo', $demo->getAttribute('demo'));

        $demo->setAttribute('demo', 'changed');
        $this->assertEquals('changed', $demo->getAttribute('demo'));
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
        $this->assertEquals($parent, $demo->parent());
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

        $this->assertEquals($parent, $demo->parent());
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
        $this->assertEquals($parent2, $demo->parent());
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
        $this->assertEquals([], $children);

        $demo->insertAfter($child1)->insertAfter($child2)->insertAfter($child3);

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertEquals([$child1, $child2, $child3], $children);

        // test depth
        $deep = $this->demoElement()->insertAfter($this->demoComment());
        $demo->insertAfter($deep);

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertEquals([$child1, $child2, $child3, $deep], $children);
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
        $child3 = $this->demoText();

        $demo->insertAfter($child1)->insertAfter($child2)->insertAfter($child3);

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertEquals([$child1, $child2, $child3], $children);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($child2));
        $this->assertTrue($demo->isChild($child3));
        $this->assertEquals($demo, $child1->parent());
        $this->assertEquals($demo, $child2->parent());
        $this->assertEquals($demo, $child3->parent());

        // test anchored
        $demo->clear()
            ->insertAfter($child1)
            ->insertAfter($child2)
            ->insertAfter($child3, $child1);

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertEquals([$child1, $child3, $child2], $children);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($child2));
        $this->assertTrue($demo->isChild($child3));
        $this->assertEquals($demo, $child1->parent());
        $this->assertEquals($demo, $child2->parent());
        $this->assertEquals($demo, $child3->parent());

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
        $this->assertEquals([$child1, $deep], $children);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($deep));
        $this->assertFalse($demo->isChild($child2));
        $this->assertEquals($demo, $child1->parent());
        $this->assertEquals($demo, $deep->parent());
        $this->assertEquals($deep, $child2->parent());
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
        $child3 = $this->demoText();

        $demo->insertBefore($child1)->insertBefore($child2)->insertBefore($child3);

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertEquals([$child3, $child2, $child1], $children);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($child2));
        $this->assertTrue($demo->isChild($child3));
        $this->assertEquals($demo, $child1->parent());
        $this->assertEquals($demo, $child2->parent());
        $this->assertEquals($demo, $child3->parent());

        // test anchored
        $demo->clear()
            ->insertBefore($child1)
            ->insertBefore($child2)
            ->insertBefore($child3, $child1);

        $children = [];
        foreach ($demo as $child) {
            $children[] = $child;
        }
        $this->assertEquals([$child2, $child3, $child1], $children);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($child2));
        $this->assertTrue($demo->isChild($child3));
        $this->assertEquals($demo, $child1->parent());
        $this->assertEquals($demo, $child2->parent());
        $this->assertEquals($demo, $child3->parent());

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
        $this->assertEquals([$deep, $child1], $children);
        $this->assertTrue($demo->isChild($child1));
        $this->assertTrue($demo->isChild($deep));
        $this->assertFalse($demo->isChild($child2));
        $this->assertEquals($demo, $child1->parent());
        $this->assertEquals($demo, $deep->parent());
        $this->assertEquals($deep, $child2->parent());
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

        $this->assertEquals($parent, $child1->parent());
        $this->assertEquals($parent, $child2->parent());
        $this->assertEquals($child2, $deepChild->parent());
        $this->assertNull($notChild->parent());
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