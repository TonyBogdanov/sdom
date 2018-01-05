<?php

namespace SDom\Test\Node;

use Kevintweber\HtmlTokenizer\HtmlTokenizer;
use PHPUnit\Framework\TestCase;
use SDom\Dom;
use SDom\Node\CData;
use SDom\Node\Comment;
use SDom\Node\DocType;
use SDom\Node\Element;
use SDom\Node\NodeInterface;
use SDom\Node\Text;
use SDom\Test\Helper\DemoGeneratorTrait;

/**
 * Class DomTest
 *
 * @coversDefaultClass \SDom\Dom
 *
 * @package SDom\Test\Node
 */
class DomTest extends TestCase
{
    use DemoGeneratorTrait;

    /**
     * @param Dom $dom
     * @return NodeInterface[]
     */
    protected function nodes(Dom $dom): array
    {
        $reflection = (new \ReflectionClass($dom))->getMethod('get');
        $reflection->setAccessible(true);
        return $reflection->invoke($dom);
    }

    /**
     * @return array[]
     */
    public function demoNodes(): array
    {
        return [
            [$this->demoCData()],
            [$this->demoComment()],
            [$this->demoDocType()],
            [$this->demoText()],
            [$this->demoElement()],
            [$this->demoVoidElement()]
        ];
    }

    /**
     * @return array
     */
    public function demoHTML(): array
    {
        return [
            ['<![CDATA[demo]]>', CData::class, 'demo', null, null],
            ['<!--demo-->', Comment::class, 'demo', null, null],
            ['<!DOCTYPE demo>', DocType::class, 'demo', null, null],
            ['demo', Text::class, 'demo', null, null],
            ['<demo a="b" c="d" e></demo>', Element::class, 'demo', false, ['a' => 'b', 'c' => 'd', 'e' => '']],
            ['<hr a="b" c="d" e/>', Element::class, 'hr', true, ['a' => 'b', 'c' => 'd', 'e' => '']]
        ];
    }

    /**
     * @return array[]
     */
    public function demoInvalidContent(): array
    {
        return [
            [true],
            [false],
            [123],
            [-123],
            [1.23],
            [-1.23],
            [[]],
            [['<div />']], // an array - still invalid
            [new \stdClass()]
        ];
    }

    /**
     * @return array[]
     */
    public function demoInvalidHTML(): array
    {
        return [
            ['<div'],
            ['<div attr=" />'],
            ['<div attr=\'" />'],
            ['<div //>'],
            ['<div><//div>']
        ];
    }

    /**
     * Test createInvalidContentException() returns an \InvalidArgumentException.
     *
     * @covers ::createInvalidContentException()
     * @dataProvider demoInvalidContent()
     *
     * @param $content
     */
    public function testCreateInvalidContentException($content)
    {
        $reflection = (new \ReflectionClass(Dom::class))->getMethod('createInvalidContentException');
        $reflection->setAccessible(true);

        $this->assertInstanceOf(\InvalidArgumentException::class, $reflection->invoke(null, $content));
    }

    /**
     * Test construct or add with no argument or from NULL.
     *
     * @param bool $construct
     */
    public function performTestConstructOrAddNull(bool $construct)
    {
        $dom = new Dom(); // can't test add() with no argument
        $this->assertEquals([], $this->nodes($dom));

        $dom = $construct ? new Dom(null) : (new Dom())->add(null);
        $this->assertEquals([], $this->nodes($dom));
    }

    /**
     * Test construct or add from another Dom.
     *
     * @param bool $construct
     */
    public function performTestConstructOrAddDom(bool $construct)
    {
        $cData = $this->demoCData();
        $comment = $this->demoComment();
        $text = $this->demoText();

        $dom = (new Dom())
            ->add($cData)
            ->add($comment)
            ->add($text);

        $new = $construct ? new Dom($dom) : (new Dom())->add($dom);
        $this->assertEquals([$cData, $comment, $text], $this->nodes($new));
    }

    /**
     * Test construct or add from NodeInterface.
     *
     * @param bool $construct
     * @param NodeInterface $node
     */
    public function performTestConstructOrAddNodeInterface(bool $construct, NodeInterface $node)
    {
        $dom = $construct ? new Dom($node) : (new Dom())->add($node);
        $this->assertEquals([$node], $this->nodes($dom));
    }

    /**
     * Test construct or add from Token.
     *
     * @param bool $construct
     * @param string $html
     * @param string $nodeClass
     * @param string $nodeContentOrTag
     * @param bool|null $elementIsVoid
     * @param array|null $elementAttributes
     * @param Dom|null $testedDom
     */
    public function performTestConstructOrAddToken(
        bool $construct,
        string $html,
        string $nodeClass,
        string $nodeContentOrTag,
        bool $elementIsVoid = null,
        array $elementAttributes = null,
        Dom $testedDom = null
    ) {
        if (!isset($testedDom)) {
            $token = (new HtmlTokenizer())->parse($html)[0];
            $testedDom = $construct ? new Dom($token) : (new Dom())->add($token);
        }

        $isElement = is_a($nodeClass, Element::class, true);

        $this->assertCount(1, $testedDom);

        $element = $testedDom->get(0);
        $this->assertInstanceOf($nodeClass, $element);

        $cotProperty = (new \ReflectionClass($element))->getProperty($isElement ? 'tag' : 'content');
        $cotProperty->setAccessible(true);

        $this->assertEquals($nodeContentOrTag, $cotProperty->getValue($element));

        if (!$isElement) {
            return;
        }

        /** @var Element $element */

        if (isset($elementIsVoid)) {
            $this->assertEquals($elementIsVoid, $element->isVoid());
        }

        if (isset($elementAttributes)) {
            /**
             * @var string $name
             * @var string $value
             */
            foreach ($elementAttributes as $name => $value) {
                $this->assertTrue($element->hasAttribute($name));
                $this->assertEquals($value, $element->getAttribute($name));
            }
        }
    }

    /**
     * Test construct or add from TokenCollection.
     *
     * @param bool $construct
     */
    public function performTestConstructOrAddTokenCollection(bool $construct)
    {
        $demo = $this->demoHTML();
        $html = '';

        foreach ($demo as $item) {
            $html .= $item[0];
        }

        $tokenCollection = (new HtmlTokenizer())->parse($html);
        $dom = $construct ? new Dom($tokenCollection) : (new Dom())->add($tokenCollection);

        $this->assertCount(count($demo), $dom);

        for ($i = 0, $c = count($demo); $i < $c; $i++) {
            $this->performTestConstructOrAddToken(...array_merge([$construct], $demo[$i], [$dom->eq($i)]));
        }
    }

    /**
     * Test construct or add from empty string or one with only whitespace. Should yield empty collection.
     *
     * @param bool $construct
     */
    public function performTestConstructOrAddWhitespace(bool $construct)
    {
        foreach (['', ' ',  "\t", "\n", "\r", "\0", "\x0B"] as $content) {
            $html = str_repeat($content, mt_rand(1, 10));
            $this->assertCount(0, $construct ? new Dom($html) : (new Dom())->add($html));
        }
    }

    /**
     * Test construct or add from HTML.
     *
     * @param bool $construct
     */
    public function performTestConstructOrAddHTML(bool $construct)
    {
        $demo = $this->demoHTML();
        $html = '';

        foreach ($demo as $item) {
            $html .= $item[0];
        }

        $dom = $construct ? new Dom($html) : (new Dom())->add($html);

        $this->assertCount(count($demo), $dom);

        for ($i = 0, $c = count($demo); $i < $c; $i++) {
            $this->performTestConstructOrAddToken(...array_merge([$construct], $demo[$i], [$dom->eq($i)]));
        }
    }

    /**
     * Test construct or add from invalid HTML.
     *
     * @param bool $construct
     * @param string $html
     */
    public function performTestConstructOrAddInvalidHTML(bool $construct, string $html)
    {
        $construct ? new Dom($html) : (new Dom())->add($html);
    }

    /**
     * Test construct or add from invalid content.
     *
     * @param bool $construct
     * @param $content
     */
    public function performTestConstructOrAddInvalidArgument(bool $construct, $content)
    {
        $construct ? new Dom($content) : (new Dom())->add($content);
    }

    /**
     * Test construct with no argument or from NULL.
     *
     * @covers ::__construct()
     */
    public function testConstructNull()
    {
        $this->performTestConstructOrAddNull(true);
    }

    /**
     * Test construct from another Dom.
     *
     * @covers ::__construct()
     */
    public function testConstructDom()
    {
        $this->performTestConstructOrAddDom(true);
    }

    /**
     * Test construct from NodeInterface.
     *
     * @dataProvider demoNodes()
     * @covers ::__construct()
     *
     * @param NodeInterface $node
     */
    public function testConstructNodeInterface(NodeInterface $node)
    {
        $this->performTestConstructOrAddNodeInterface(true, $node);
    }

    /**
     * Test construct from Token.
     *
     * @dataProvider demoHTML()
     * @covers ::__construct()
     *
     * @param string $html
     * @param string $nodeClass
     * @param string $nodeContentOrTag
     * @param bool|null $elementIsVoid
     * @param array|null $elementAttributes
     * @param Dom|null $testedDom
     */
    public function testConstructToken(
        string $html,
        string $nodeClass,
        string $nodeContentOrTag,
        bool $elementIsVoid = null,
        array $elementAttributes = null,
        Dom $testedDom = null
    ) {
        $this->performTestConstructOrAddToken(
            true,
            $html,
            $nodeClass,
            $nodeContentOrTag,
            $elementIsVoid,
            $elementAttributes,
            $testedDom
        );
    }

    /**
     * Test construct from TokenCollection.
     *
     * @covers ::__construct()
     */
    public function testConstructTokenCollection()
    {
        $this->performTestConstructOrAddTokenCollection(true);
    }

    /**
     * Test construct from empty string or one with only whitespace. Should yield empty collection.
     *
     * @covers ::__construct()
     */
    public function testConstructWhitespace()
    {
        $this->performTestConstructOrAddWhitespace(true);
    }

    /**
     * Test construct from HTML.
     *
     * @covers ::__construct()
     */
    public function testConstructHTML()
    {
        $this->performTestConstructOrAddHTML(true);
    }

    /**
     * Test construct from invalid HTML.
     *
     * @covers ::__construct()
     * @dataProvider demoInvalidHTML()
     * @expectedException \InvalidArgumentException
     *
     * @param string $html
     */
    public function testConstructInvalidHTML(string $html)
    {
        $this->performTestConstructOrAddInvalidHTML(true, $html);
    }

    /**
     * Test construct from invalid content.
     *
     * @dataProvider demoInvalidContent()
     * @covers ::__construct()
     * @expectedException \InvalidArgumentException
     *
     * @param $content
     */
    public function testConstructInvalidArgument($content)
    {
        $this->performTestConstructOrAddInvalidArgument(true, $content);
    }

    /**
     * Test add with no argument or from NULL.
     *
     * @covers ::add()
     */
    public function testAddNull()
    {
        $this->performTestConstructOrAddNull(false);
    }

    /**
     * Test add from another Dom.
     *
     * @covers ::add()
     */
    public function testAddDom()
    {
        $this->performTestConstructOrAddDom(false);
    }

    /**
     * Test add from NodeInterface.
     *
     * @dataProvider demoNodes()
     * @covers ::add()
     *
     * @param NodeInterface $node
     */
    public function testAddNodeInterface(NodeInterface $node)
    {
        $this->performTestConstructOrAddNodeInterface(false, $node);
    }

    /**
     * Test add from Token.
     *
     * @dataProvider demoHTML()
     * @covers ::add()
     *
     * @param string $html
     * @param string $nodeClass
     * @param string $nodeContentOrTag
     * @param bool|null $elementIsVoid
     * @param array|null $elementAttributes
     * @param Dom|null $testedDom
     */
    public function testAddToken(
        string $html,
        string $nodeClass,
        string $nodeContentOrTag,
        bool $elementIsVoid = null,
        array $elementAttributes = null,
        Dom $testedDom = null
    ) {
        $this->performTestConstructOrAddToken(
            false,
            $html,
            $nodeClass,
            $nodeContentOrTag,
            $elementIsVoid,
            $elementAttributes,
            $testedDom
        );
    }

    /**
     * Test add from TokenCollection.
     *
     * @covers ::add()
     */
    public function testAddTokenCollection()
    {
        $this->performTestConstructOrAddTokenCollection(false);
    }

    /**
     * Test add from empty string or one with only whitespace. Should yield empty collection.
     *
     * @covers ::add()
     */
    public function testAddWhitespace()
    {
        $this->performTestConstructOrAddWhitespace(false);
    }

    /**
     * Test add from HTML.
     *
     * @covers ::add()
     */
    public function testAddHTML()
    {
        $this->performTestConstructOrAddHTML(false);
    }

    /**
     * Test add from invalid HTML.
     *
     * @covers ::add()
     * @dataProvider demoInvalidHTML()
     * @expectedException \InvalidArgumentException
     *
     * @param string $html
     */
    public function testAddInvalidHTML(string $html)
    {
        $this->performTestConstructOrAddInvalidHTML(false, $html);
    }

    /**
     * Test add from invalid content.
     *
     * @dataProvider demoInvalidContent()
     * @covers ::add()
     * @expectedException \InvalidArgumentException
     *
     * @param $content
     */
    public function testAddInvalidArgument($content)
    {
        $this->performTestConstructOrAddInvalidArgument(false, $content);
    }

    /**
     * Test \IteratorAggregate.
     *
     * @covers ::getIterator()
     */
    public function testGetIterator()
    {
        $nodes = [
            $this->demoCData(),
            $this->demoComment(),
            $this->demoText()
        ];

        $dom = (new Dom())
            ->add($nodes[0])
            ->add($nodes[1])
            ->add($nodes[2]);

        /**
         * @var int $i
         * @var Dom $subDom
         */
        foreach ($dom as $i => $subDom) {
            $this->assertInstanceOf(Dom::class, $subDom);
            $this->assertCount(1, $subDom);
            $this->assertEquals($nodes[$i], $subDom->get(0));
        }
    }

    /**
     * Test \Countable.
     *
     * @covers ::count()
     */
    public function testCount()
    {
        $this->assertCount(0, new Dom());
        $this->assertCount(1, new Dom('<div />'));
        $this->assertCount(2, new Dom('<div /><div />'));
    }

    /**
     * Test if get returns proper value(s).
     *
     * @covers ::get()
     */
    public function testGet()
    {
        $cData = $this->demoCData();
        $comment = $this->demoComment();

        $dom = (new Dom())
            ->add($cData)
            ->add($comment);

        $this->assertEquals($cData, $dom->get(0));
        $this->assertEquals($comment, $dom->get(1));
        $this->assertEquals([$cData, $comment], $dom->get());
    }

    /**
     * Test if requesting a non-existent index in get() throws an exception.
     *
     * @covers ::get()
     * @expectedException \OutOfBoundsException
     */
    public function testGetOutOfBounds()
    {
        (new Dom($this->demoComment()))->get(1);
    }

    /**
     * Test if requesting a non-existent index in get() throws an exception.
     *
     * @covers ::get()
     * @expectedException \OutOfBoundsException
     */
    public function testGetOutOfBoundsNegative()
    {
        (new Dom())->get(-1);
    }

    /**
     * Test if clear() removes all nodes from the collection.
     *
     * @covers ::clear()
     */
    public function testClear()
    {
        $this->assertCount(0, (new Dom('<div /><div />'))->clear());
    }

    /**
     * Test if eq() retrieves proper result.
     *
     * @covers ::eq()
     */
    public function testEq()
    {
        $cData = $this->demoCData();
        $comment = $this->demoComment();
        $text = $this->demoText();

        $dom = (new Dom())
            ->add($cData)
            ->add($comment)
            ->add($text);

        $eq0 = $dom->eq(0);
        $eq1 = $dom->eq(1);
        $eq2 = $dom->eq(2);
        $eq3 = $dom->eq(3);

        $this->assertInstanceOf(Dom::class, $eq0);
        $this->assertInstanceOf(Dom::class, $eq1);
        $this->assertInstanceOf(Dom::class, $eq2);
        $this->assertInstanceOf(Dom::class, $eq3);

        $this->assertCount(1, $eq0);
        $this->assertCount(1, $eq1);
        $this->assertCount(1, $eq2);
        $this->assertCount(0, $eq3);

        $this->assertEquals($cData, $eq0->get(0));
        $this->assertEquals($comment, $eq1->get(0));
        $this->assertEquals($text, $eq2->get(0));
    }

    /**
     * Test if first() retrieves proper result.
     *
     * @covers ::first()
     */
    public function testFirst()
    {
        $nodes = [
            $this->demoCData(),
            $this->demoComment(),
            $this->demoText()
        ];

        $dom = (new Dom())
            ->add($nodes[0])
            ->add($nodes[1])
            ->add($nodes[2]);

        $first = $dom->first();

        $this->assertInstanceOf(Dom::class, $first);
        $this->assertCount(1, $first);
        $this->assertEquals($nodes[0], $first->get(0));
    }

    /**
     * Test if last() retrieves proper result.
     *
     * @covers ::last()
     */
    public function testLast()
    {
        $nodes = [
            $this->demoCData(),
            $this->demoComment(),
            $this->demoText()
        ];

        $dom = (new Dom())
            ->add($nodes[0])
            ->add($nodes[1])
            ->add($nodes[2]);

        $last = $dom->last();

        $this->assertInstanceOf(Dom::class, $last);
        $this->assertCount(1, $last);
        $this->assertEquals($nodes[2], $last->get(0));
    }
}