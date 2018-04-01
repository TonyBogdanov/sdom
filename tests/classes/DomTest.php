<?php

namespace SDom\Test\Node;

use Kevintweber\HtmlTokenizer\Tokens;
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
     * @param array[] $tree
     * @param Dom $dom
     */
    protected function assertTree(array $tree, Dom $dom)
    {
        $this->assertCount(count($tree), $dom);

        /**
         * @var int $index
         * @var array $entry
         */
        foreach ($tree as $index => $entry) {
            /**
             * @var NodeInterface $node
             * @var array[]
             */
            list($node, $children) = $entry;

            $this->assertSame($node, $dom->get($index));

            if (0 < count($children)) {
                $this->assertTree($children, $dom->eq($index)->children());
            }
        }
    }

    /**
     * @param array $expected
     * @return array
     */
    protected function demoNoFilter(array $expected): array
    {
        return $expected;
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
    public function demoContent(): array
    {
        return array_merge([
            [null, function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([]), $nodes);
            }],
            [$this->demoCData(), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([$content]), $nodes);
            }],
            [$this->demoComment(), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([$content]), $nodes);
            }],
            [$this->demoDocType(), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([$content]), $nodes);
            }],
            [$this->demoText(), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([$content]), $nodes);
            }],
            [$this->demoElement(), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([$content]), $nodes);
            }],
            [$this->demoElement(true), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([$content]), $nodes);
            }],
            [$this->demoElement(false, true), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([$content]), $nodes);
            }],
            [$this->demoElement(true, true), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([$content]), $nodes);
            }],
            [$this->demoVoidElement(), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([$content]), $nodes);
            }],
            [$this->demoVoidElement(true), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([$content]), $nodes);
            }],
            [(function () {
                $token = new Tokens\CData();
                $token->parse((string) $this->demoCData());
                return $token;
            })(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $filter([$this->demoCData()])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(function () {
                $token = new Tokens\Comment();
                $token->parse((string) $this->demoComment());
                return $token;
            })(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $filter([$this->demoComment()])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(function () {
                $token = new Tokens\DocType();
                $token->parse((string) $this->demoDocType());
                return $token;
            })(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $filter([$this->demoDocType()])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(function () {
                $token = new Tokens\Text();
                $token->parse((string) $this->demoText());
                return $token;
            })(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $filter([$this->demoText()])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(function () {
                $token = new Tokens\Element();
                $token->parse((string) $this->demoElement());
                return $token;
            })(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $filter([$this->demoElement()])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(function () {
                $token = new Tokens\Element();
                $token->parse((string) $this->demoElement(true));
                return $token;
            })(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $filter([$this->demoElement(true)])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(function () {
                $token = new Tokens\Element();
                $token->parse((string) $this->demoElement(false, true));
                return $token;
            })(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $filter([$this->demoElement(false, true)])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(function () {
                $token = new Tokens\Element();
                $token->parse((string) $this->demoElement(true, true));
                return $token;
            })(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $filter([$this->demoElement(true, true)])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(function () {
                $tokenCollection = new Tokens\TokenCollection();
                $index = 0;

                $token = new Tokens\CData();
                $token->parse((string) $this->demoCData());
                $tokenCollection[$index++] = $token;

                $token = new Tokens\Comment();
                $token->parse((string) $this->demoComment());
                $tokenCollection[$index++] = $token;

                $token = new Tokens\DocType();
                $token->parse((string) $this->demoDocType());
                $tokenCollection[$index++] = $token;

                $token = new Tokens\Text();
                $token->parse((string) $this->demoText());
                $tokenCollection[$index++] = $token;

                $token = new Tokens\Element();
                $token->parse((string) $this->demoElement());
                $tokenCollection[$index++] = $token;

                $token = new Tokens\Element();
                $token->parse((string) $this->demoElement(true));
                $tokenCollection[$index++] = $token;

                $token = new Tokens\Element();
                $token->parse((string) $this->demoElement(false, true));
                $tokenCollection[$index++] = $token;

                $token = new Tokens\Element();
                $token->parse((string) $this->demoElement(true, true));
                $tokenCollection[$index++] = $token;

                $token = new Tokens\Element();
                $token->parse((string) $this->demoVoidElement());
                $tokenCollection[$index++] = $token;

                $token = new Tokens\Element();
                $token->parse((string) $this->demoVoidElement(true));
                $tokenCollection[$index + 1] = $token;

                return $tokenCollection;
            })(), function ($content, array $nodes, callable $filter, bool $ignoreDocType = false) {
                $this->assertSame(
                    array_map(function (NodeInterface $node) {
                        return (string) $node;
                    }, $filter(
                        array_merge([
                            $this->demoCData(),
                            $this->demoComment()
                        ], $ignoreDocType ? [] : [
                            $this->demoDocType()
                        ], [
                            $this->demoText(),
                            $this->demoElement(),
                            $this->demoElement(true),
                            $this->demoElement(false, true),
                            $this->demoElement(true, true),
                            $this->demoVoidElement(),
                            $this->demoVoidElement(true)
                        ])
                    )), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }]
        ], array_map(function ($whitespace) {
            return [str_repeat($whitespace, mt_rand(1, 10)), function ($content, array $nodes, callable $filter) {
                $this->assertSame($filter([]), $nodes);
            }];
        }, ['', ' ',  "\t", "\n", "\r", "\0", "\x0B"]), [
            [(string) $this->demoCData(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function ($something) {
                    return (string) $something;
                }, $filter([$content])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(string) $this->demoComment(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function ($something) {
                    return (string) $something;
                }, $filter([$content])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(string) $this->demoDocType(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function ($something) {
                    return (string) $something;
                }, $filter([$content])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(string) $this->demoText(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function ($something) {
                    return (string) $something;
                }, $filter([$content])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(string) $this->demoElement(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function ($something) {
                    return (string) $something;
                }, $filter([$content])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(string) $this->demoElement(true), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function ($something) {
                    return (string) $something;
                }, $filter([$content])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(string) $this->demoElement(false, true), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function ($something) {
                    return (string) $something;
                }, $filter([$content])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(string) $this->demoElement(true, true), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function ($something) {
                    return (string) $something;
                }, $filter([$content])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(string) $this->demoVoidElement(), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function ($something) {
                    return (string) $something;
                }, $filter([$content])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }],
            [(string) $this->demoVoidElement(true), function ($content, array $nodes, callable $filter) {
                $this->assertSame(array_map(function ($something) {
                    return (string) $something;
                }, $filter([$content])), array_map(function (NodeInterface $node) {
                    return (string) $node;
                }, $nodes));
            }], [
                (new Dom())
                    ->add($this->demoCData())
                    ->add($this->demoComment())
                    ->add($this->demoDocType())
                    ->add($this->demoText())
                    ->add($this->demoElement())
                    ->add($this->demoElement(true))
                    ->add($this->demoElement(false, true))
                    ->add($this->demoElement(true, true))
                    ->add($this->demoVoidElement())
                    ->add($this->demoVoidElement(true)),
                function (Dom $content, array $nodes, callable $filter) {
                    $this->assertSame($filter(array_map(function (int $i) use ($content) {
                        return $content->get($i);
                    }, range(0, count($content) - 1))), $nodes);
                }
            ]
        ]);
    }

    /**
     * @return array
     */
    public function demoContentNoDocType(): array
    {
        $docTypeHTML = (string) $this->demoDocType();

        $demo = $this->demoContent();
        $newDemo = [];

        foreach ($demo as $item) {
            if (
                $item[0] instanceof DocType ||
                $item[0] instanceof Tokens\DocType ||
                $item[0] === $docTypeHTML
            ) {
                continue;
            }

            if ($item[0] instanceof Tokens\TokenCollection) {
                /**
                 * @var int $index
                 * @var Tokens\Token $token
                 */
                foreach ($item[0] as $index => $token) {
                    if ($token instanceof Tokens\DocType) {
                        unset($item[0][$index]);
                    }
                }

                $callback = $item[1];
                $item[1] = function ($content, array $nodes, callable $filter) use ($callback) {
                    $callback($content, $nodes, $filter, true);
                };
            } else if ($item[0] instanceof Dom) {
                $dom = new Dom();

                /** @var NodeInterface $node */
                foreach ($item[0]->get() as $node) {
                    if (!$node instanceof DocType) {
                        $dom->add($node);
                    }
                }

                $item[0] = $dom;
            }

            $newDemo[] = $item;
        }

        return $newDemo;
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
     * @return array
     */
    public function demoHTML(): array
    {
        return [
            [(string) $this->demoCData(), CData::class, 'demo', null, null],
            [(string) $this->demoComment(), Comment::class, 'demo', null, null],
            [(string) $this->demoDocType(), DocType::class, 'demo', null, null],
            [(string) $this->demoText(), Text::class, 'demo', null, null],
            [(string) $this->demoElement(true), Element::class, 'demo', false, ['a' => 'b', 'c' => 'd', 'e' => '']],
            [(string) $this->demoVoidElement(true), Element::class, 'br', true, ['a' => 'b', 'c' => 'd', 'e' => '']]
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
     * @throws \ReflectionException
     */
    public function testCreateInvalidContentException($content)
    {
        $reflection = (new \ReflectionClass(Dom::class))->getMethod('createInvalidContentException');
        $reflection->setAccessible(true);

        $this->assertInstanceOf(\InvalidArgumentException::class, $reflection->invoke(null, $content));
    }

    /**
     * Test testFindFirstElement() properly finds the first element node or returns NULL.
     *
     * @covers ::findFirstElement()
     *
     * @throws \ReflectionException
     */
    public function testFindFirstElement()
    {
        $element = $this->demoElement();
        $goodNodes = [
            $this->demoCData(),
            $this->demoComment(),
            $this->demoDocType(),
            $this->demoText(),
            $element,
            $this->demoVoidElement()
        ];
        $badNodes = [
            $this->demoCData(),
            $this->demoComment(),
            $this->demoDocType(),
        ];

        $reflection = (new \ReflectionClass(Dom::class))->getMethod('findFirstElement');
        $reflection->setAccessible(true);

        $this->assertSame($element, $reflection->invoke(null, $goodNodes));
        $this->assertNull($reflection->invoke(null, $badNodes));
    }

    /**
     * Test findFirstInnermostElement() properly finds the first inner-most element node or returns NULL.
     *
     * @covers ::findFirstInnermostElement()
     *
     * @throws \ReflectionException
     */
    public function testFindFirstInnermostElement()
    {
        $element = $this->demoElement();
        $goodTree = $this->demoElement()
            ->insertAfter($this->demoComment())
            ->insertAfter(
                $this->demoElement()
                    ->insertAfter($this->demoComment())
                    ->insertAfter(
                        $this->demoElement()
                            ->insertAfter($this->demoComment())
                            ->insertAfter($element)
                            ->insertAfter($this->demoElement())
                    )->insertAfter($this->demoElement())
            )->insertAfter($this->demoElement());
        $badTree = $this->demoElement()
            ->insertAfter($this->demoCData())
            ->insertAfter($this->demoComment())
            ->insertAfter($this->demoText());

        $reflection = (new \ReflectionClass(Dom::class))->getMethod('findFirstInnermostElement');
        $reflection->setAccessible(true);

        $this->assertSame($element, $reflection->invoke(null, $goodTree->getIterator()->getArrayCopy()));
        $this->assertNull($reflection->invoke(null, $badTree->getIterator()->getArrayCopy()));
    }

    /**
     * Test constructing a Dom instance from various content types.
     *
     * @covers ::__construct()
     * @dataProvider demoContent()
     *
     * @param $content
     * @param callable $assert
     */
    public function testConstruct($content, callable $assert)
    {
        $dom = new Dom($content);
        $assert($content, $dom->get(), [$this, 'demoNoFilter']);
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
        (new Dom($html));
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
        (new Dom($content));
    }

    /**
     * Test add()-ing to an empty Dom instance from various content types.
     *
     * @dataProvider demoContent()
     *
     * @param $content
     * @param callable $assert
     */
    public function testAdd($content, callable $assert)
    {
        $dom = (new Dom())->add($content);
        $assert($content, $dom->get(), [$this, 'demoNoFilter']);
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
        (new Dom())->add($html);
    }

    /**
     * Test add from invalid content.
     *
     * @covers ::add()
     * @dataProvider demoInvalidContent()
     * @expectedException \InvalidArgumentException
     *
     * @param $content
     */
    public function testAddInvalidArgument($content)
    {
        (new Dom())->add($content);
    }

    /**
     * Test that add() allows duplicate nodes, but ignores duplicate node instances.
     *
     * @covers ::add()
     */
    public function testAddDuplicates()
    {
        $cData = $this->demoCData();
        $comment1 = $this->demoComment();
        $comment2 = $this->demoComment();

        $dom = (new Dom())
            ->add($cData)
            ->add($comment1)
            ->add($comment2)
            ->add($comment1)
            ->add($comment2);

        $this->assertCount(3, $dom);
        $this->assertSame([$cData, $comment1, $comment2], $dom->get());
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
            $this->assertSame($nodes[$i], $subDom->get(0));
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
        $this->assertCount(1, new Dom((string) $this->demoElement()));
        $this->assertCount(2, new Dom((string) $this->demoElement() . (string) $this->demoElement()));
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

        $this->assertSame($cData, $dom->get(0));
        $this->assertSame($comment, $dom->get(1));
        $this->assertSame([$cData, $comment], $dom->get());
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

        $this->assertSame($cData, $eq0->get(0));
        $this->assertSame($comment, $eq1->get(0));
        $this->assertSame($text, $eq2->get(0));
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
        $this->assertSame($nodes[0], $first->get(0));
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
        $this->assertSame($nodes[2], $last->get(0));
    }

    /**
     * Test if children() retrieves proper result.
     *
     * @covers ::children()
     */
    public function testChildren()
    {
        $cData = $this->demoCData();
        $comment1 = $this->demoComment();
        $comment2 = $this->demoComment();
        $comment3 = $this->demoComment();

        $element1 = $this->demoElement()
            ->insertAfter($cData)
            ->insertAfter($comment1);
        $element2 = $this->demoElement()
            ->insertAfter($comment2);

        $dom = (new Dom())
            ->add($element1)
            ->add($element2)
            ->add($comment3);

        $this->assertSame([$cData, $comment1, $comment2], $dom->children()->get());
    }

    /**
     * Test append() against various content types.
     *
     * @covers ::append()
     * @dataProvider demoContentNoDocType()
     *
     * @param $content
     * @param callable $assert
     */
    public function testAppend($content, callable $assert)
    {
        $dom = (new Dom($this->demoElement()))
            ->append($node = $this->demoCData())
            ->append($content);

        $assert($content, $dom->children()->get(), function (array $expected) use ($node) {
            return array_merge([$node], $expected);
        });
    }

    /**
     * Test if complex tree structure is handled properly by append() in multi-element collections.
     *
     * @covers ::append()
     */
    public function testAppendMultiple()
    {
        $element1 = new Element('element1');
        $element2 = new Element('element2');

        $dummy1 = new Element('dummy1');
        $dummy2 = new Element('dummy2');

        $child1 = new Element('child1');
        $child2 = new Element('child2');
        $childDeep = new Element('child_deep');

        $element1->insertAfter($dummy1);
        $element2->insertAfter($dummy2);
        $child2->insertAfter($childDeep);

        $parentDom = (new Dom($element1))->add($element2);
        $childDom = (new Dom($child1))->add($child2);

        $parentDom->append($childDom);

        $this->assertCount(2, $parentDom);
        $this->assertCount(3, $parentDom->eq(0)->children());
        $this->assertCount(3, $parentDom->eq(1)->children());

        $this->assertSame($dummy1, $parentDom->eq(0)->children()->get(0));
        $this->assertSame($child1, $parentDom->eq(0)->children()->get(1));
        $this->assertSame($child2, $parentDom->eq(0)->children()->get(2));
        $this->assertSame($childDeep, $parentDom->eq(0)->children()->eq(2)->children()->get(0));

        // the second element from the collection receives cloned copies
        $this->assertSame($dummy2, $parentDom->eq(1)->children()->get(0));
        $this->assertNotSame($child1, $parentDom->eq(1)->children()->get(1));
        $this->assertNotSame($child2, $parentDom->eq(1)->children()->get(2));
        $this->assertNotSame($childDeep, $parentDom->eq(1)->children()->eq(2)->children()->get(0));
        $this->assertSame((string) $child1, (string) $parentDom->eq(1)->children()->get(1));
        $this->assertSame((string) $child2, (string) $parentDom->eq(1)->children()->get(2));
        $this->assertSame((string) $childDeep, (string) $parentDom->eq(1)->children()->eq(2)->children()->get(0));
    }

    /**
     * Test prepend() against various content types.
     *
     * @covers ::prepend()
     * @dataProvider demoContentNoDocType()
     *
     * @param $content
     * @param callable $assert
     */
    public function testPrepend($content, callable $assert)
    {
        $dom = (new Dom($this->demoElement()))
            ->append($node = $this->demoCData())
            ->prepend($content);

        $assert($content, $dom->children()->get(), function (array $expected) use ($node) {
            return array_merge($expected, [$node]);
        });
    }

    /**
     * Test wrap() against a complex structure.
     *
     * @covers ::wrap()
     *
     * @return array
     */
    public function testWrap(): array
    {
        $assertChainIntact = function ($chain, $child1, $child2, $child3) {
            $this->assertCount(3, $chain);
            $this->assertSame($child1, $chain->get(0));
            $this->assertSame($child2, $chain->get(1));
            $this->assertSame($child3, $chain->get(2));
        };

        $assertWrapperIntact = function (
            $wrapper,
            $comment,
            $wrapperChild1,
            $wrapperChild2,
            $wrapperChild1Child1,
            $wrapperChild1Child2,
            $wrapperChild2Child1,
            $wrapperChild2Child2
        ) {
            $this->assertCount(3, $wrapper);
            $this->assertSame($comment, $wrapper->get(0));
            $this->assertSame($wrapperChild1, $wrapper->get(1));
            $this->assertSame($wrapperChild2, $wrapper->get(2));
            $this->assertCount(2, $wrapperChild1);
            $this->assertSame($wrapperChild1Child1, $wrapperChild1->get(0));
            $this->assertSame($wrapperChild1Child2, $wrapperChild1->get(1));
            $this->assertCount(0, $wrapperChild1Child1);
            $this->assertCount(0, $wrapperChild1Child2);
            $this->assertCount(2, $wrapperChild2);
            $this->assertSame($wrapperChild2Child1, $wrapperChild2->get(0));
            $this->assertSame($wrapperChild2Child2, $wrapperChild2->get(1));
            $this->assertCount(0, $wrapperChild2Child1);
            $this->assertCount(0, $wrapperChild2Child2);
        };

        $parent = new Element('parent');

        $child1 = new Element('child1');
        $child1Child1 = new Element('child1child1');
        $child1Child2 = new Element('child1child2');

        $child2 = new Element('child2');
        $child2Child1 = new Element('child2child1');
        $child2Child2 = new Element('child2child2');
        $child2Comment = new Comment('comment');

        $outer = new Element('outer');
        $wrapper = new Element('wrapper');
        $comment = new Comment('comment');

        $wrapperChild1 = new Element('wrapperchild1');
        $wrapperChild1Child1 = new Element('wrapperchild1child1');
        $wrapperChild1Child2 = new Element('wrapperchild1child2');

        $wrapperChild2 = new Element('wrapperchild2');
        $wrapperChild2Child1 = new Element('wrapperchild2child1');
        $wrapperChild2Child2 = new Element('wrapperchild2child2');

        $parent->insertAfter($child1)->insertAfter($child2);
        $child1->insertAfter($child1Child1)->insertAfter($child1Child2);
        $child2->insertAfter($child2Child1)->insertAfter($child2Child2)->insertAfter($child2Comment);

        $outer->insertAfter($wrapper);
        $wrapper->insertAfter($comment)->insertAfter($wrapperChild1)->insertAfter($wrapperChild2);
        $wrapperChild1->insertAfter($wrapperChild1Child1)->insertAfter($wrapperChild1Child2);
        $wrapperChild2->insertAfter($wrapperChild2Child1)->insertAfter($wrapperChild2Child2);

        $chain = (new Dom($child2Child1))
            ->add($child2Child2)
            ->add($child2Comment)
            ->wrap(new Dom($wrapper));

        // assert that the chain still points to the original collection
        $assertChainIntact($chain, $child2Child1, $child2Child2, $child2Comment);

        // assert that the original wrapper structure is intact
        $assertWrapperIntact($wrapper, $comment, $wrapperChild1, $wrapperChild2, $wrapperChild1Child1,
            $wrapperChild1Child2, $wrapperChild2Child1, $wrapperChild2Child2);

        // assert the new structure
        $this->assertCount(2, $parent);
        $this->assertSame($child1, $parent->get(0));
        $this->assertSame($child2, $parent->get(1));
        $this->assertCount(2, $child1);
        $this->assertSame($child1Child1, $child1->get(0));
        $this->assertSame($child1Child2, $child1->get(1));
        $this->assertCount(0, $child1Child1);
        $this->assertCount(0, $child1Child2);
        $this->assertCount(3, $child2);

        // the changed part
        // wrapped child 2 child 1
        $this->assertNotSame($wrapper, $child2->get(0));
        $this->assertEquals($wrapper->clone()->detach()->clear(), $child2->get(0)->clone()->detach()->clear());
        $this->assertCount(1, $child2->get(0));
        $this->assertNotSame($wrapperChild1, $child2->get(0)->get(0));
        $this->assertEquals($wrapperChild1->clone()->detach()->clear(), $child2->get(0)->get(0)->clone()->detach()
            ->clear());
        $this->assertCount(1, $child2->get(0)->get(0));
        $this->assertNotSame($wrapperChild1Child1, $child2->get(0)->get(0)->get(0));
        $this->assertEquals($wrapperChild1Child1->clone()->detach()->clear(), $child2->get(0)->get(0)->get(0)->clone()
            ->detach()->clear());
        $this->assertCount(1, $child2->get(0)->get(0)->get(0));
        $this->assertSame($child2Child1, $child2->get(0)->get(0)->get(0)->get(0));

        // wrapped child 2 child 2
        $this->assertNotSame($wrapper, $child2->get(1));
        $this->assertEquals($wrapper->clone()->detach()->clear(), $child2->get(1)->clone()->detach()->clear());
        $this->assertCount(1, $child2->get(1));
        $this->assertNotSame($wrapperChild1, $child2->get(1)->get(0));
        $this->assertEquals($wrapperChild1->clone()->detach()->clear(), $child2->get(1)->get(0)->clone()->detach()
            ->clear());
        $this->assertCount(1, $child2->get(1)->get(0));
        $this->assertNotSame($wrapperChild1Child1, $child2->get(1)->get(0)->get(0));
        $this->assertEquals($wrapperChild1Child1->clone()->detach()->clear(), $child2->get(1)->get(0)->get(0)->clone()
            ->detach()->clear());
        $this->assertCount(1, $child2->get(1)->get(0)->get(0));
        $this->assertSame($child2Child2, $child2->get(1)->get(0)->get(0)->get(0));

        // wrapped child 2 comment
        $this->assertNotSame($wrapper, $child2->get(2));
        $this->assertEquals($wrapper->clone()->detach()->clear(), $child2->get(2)->clone()->detach()->clear());
        $this->assertCount(1, $child2->get(2));
        $this->assertNotSame($wrapperChild1, $child2->get(2)->get(0));
        $this->assertEquals($wrapperChild1->clone()->detach()->clear(), $child2->get(2)->get(0)->clone()->detach()
            ->clear());
        $this->assertCount(1, $child2->get(2)->get(0));
        $this->assertNotSame($wrapperChild1Child1, $child2->get(2)->get(0)->get(0));
        $this->assertEquals($wrapperChild1Child1->clone()->detach()->clear(), $child2->get(2)->get(0)->get(0)->clone()
            ->detach()->clear());
        $this->assertCount(1, $child2->get(2)->get(0)->get(0));
        $this->assertSame($child2Comment, $child2->get(2)->get(0)->get(0)->get(0));

        return [
            $assertChainIntact,
            $assertWrapperIntact
        ];
    }

    /**
     * @covers ::wrap()
     * @depends testWrap
     *
     * @param array $wrap
     */
    public function testWrapContentHasNoElementNode(array $wrap)
    {
        $parent = new Element('parent');
        $child1 = new Element('child1');
        $child2 = new Element('child2');
        $child3 = new Element('child3');

        $parent
            ->insertAfter($child1)
            ->insertAfter($child2)
            ->insertAfter($child3);

        (new Dom($child1))
            ->add($child2)
            ->add($child3)
            ->wrap($this->demoComment());

        $wrap[0]($parent, $child1, $child2, $child3);
    }

    /**
     * @covers ::wrap()
     */
    public function testWrapContentHasNoDeeperElementNode()
    {
        $parent = new Element('parent');
        $child1 = new Element('child1');
        $child2 = new Element('child2');
        $child3 = new Element('child3');

        $wrapper = new Element('a');
        $wrapperComment = $this->demoComment();

        $parent
            ->insertAfter($child1)
            ->insertAfter($child2)
            ->insertAfter($child3);

        $wrapper
            ->insertAfter($wrapperComment);

        $chain = (new Dom($child1))
            ->add($child2)
            ->add($child3)
            ->wrap($wrapper);

        $this->assertEquals($child1->clone()->detach()->clear(), $chain->get(0)->clone()->detach()->clear());
        $this->assertEquals($child2->clone()->detach()->clear(), $chain->get(1)->clone()->detach()->clear());
        $this->assertEquals($child3->clone()->detach()->clear(), $chain->get(2)->clone()->detach()->clear());

        $this->assertNotNull($chain->get(0)->parent());
        $this->assertNotNull($chain->get(1)->parent());
        $this->assertNotNull($chain->get(2)->parent());

        $this->assertEquals($wrapper->clone()->detach()->clear(), $chain->get(0)->parent()->clone()->detach()->clear());
        $this->assertEquals($wrapper->clone()->detach()->clear(), $chain->get(1)->parent()->clone()->detach()->clear());
        $this->assertEquals($wrapper->clone()->detach()->clear(), $chain->get(2)->parent()->clone()->detach()->clear());

        $this->assertCount(2, $chain->get(0)->parent());
        $this->assertCount(2, $chain->get(1)->parent());
        $this->assertCount(2, $chain->get(2)->parent());

        $this->assertEquals($wrapperComment->clone()->detach(), $chain->get(0)->parent()->get(0)->clone()->detach());
        $this->assertEquals($wrapperComment->clone()->detach(), $chain->get(1)->parent()->get(0)->clone()->detach());
        $this->assertEquals($wrapperComment->clone()->detach(), $chain->get(2)->parent()->get(0)->clone()->detach());

        $this->assertEquals($child1->clone()->detach()->clear(), $chain->get(0)->parent()->get(1)->clone()->detach()
            ->clear());
        $this->assertEquals($child2->clone()->detach()->clear(), $chain->get(1)->parent()->get(1)->clone()->detach()
            ->clear());
        $this->assertEquals($child3->clone()->detach()->clear(), $chain->get(2)->parent()->get(1)->clone()->detach()
            ->clear());
    }

    /**
     * @covers ::wrap()
     */
    public function testWrapCollectionElementWithNoParentIsIgnored()
    {
        $parent = new Element('parent');
        $child = new Element('child');
        $foreign = new Element('foreign');

        $parent->insertAfter($child);

        (new Dom($child))
            ->add($foreign)
            ->wrap($this->demoElement());

        $this->assertNull($foreign->parent());
    }

    /**
     * Test if complex tree structure is handled properly by prepend() in multi-element collections.
     *
     * @covers ::prepend()
     */
    public function testPrependMultiple()
    {
        $element1 = new Element('element1');
        $element2 = new Element('element2');

        $dummy1 = new Element('dummy1');
        $dummy2 = new Element('dummy2');

        $child1 = new Element('child1');
        $child2 = new Element('child2');
        $childDeep = new Element('child_deep');

        $element1->insertAfter($dummy1);
        $element2->insertAfter($dummy2);
        $child2->insertAfter($childDeep);

        $parentDom = (new Dom($element1))->add($element2);
        $childDom = (new Dom($child1))->add($child2);

        $parentDom->prepend($childDom);

        $this->assertCount(2, $parentDom);
        $this->assertCount(3, $parentDom->eq(0)->children());
        $this->assertCount(3, $parentDom->eq(1)->children());

        $this->assertSame($child1, $parentDom->eq(0)->children()->get(0));
        $this->assertSame($child2, $parentDom->eq(0)->children()->get(1));
        $this->assertSame($dummy1, $parentDom->eq(0)->children()->get(2));
        $this->assertSame($childDeep, $parentDom->eq(0)->children()->eq(1)->children()->get(0));

        // the second element from the collection receives cloned copies
        $this->assertSame($dummy2, $parentDom->eq(1)->children()->get(2));
        $this->assertNotSame($child1, $parentDom->eq(1)->children()->get(0));
        $this->assertNotSame($child2, $parentDom->eq(1)->children()->get(1));
        $this->assertNotSame($childDeep, $parentDom->eq(1)->children()->eq(1)->children()->get(0));
        $this->assertSame((string) $child1, (string) $parentDom->eq(1)->children()->get(0));
        $this->assertSame((string) $child2, (string) $parentDom->eq(1)->children()->get(1));
        $this->assertSame((string) $childDeep, (string) $parentDom->eq(1)->children()->eq(1)->children()->get(0));
    }

    /**
     * @covers ::find()
     * @covers ::traverseMatch()
     */
    public function testFind()
    {
        $domToStrings = function (Dom $dom) {
            $strings = [];

            foreach ($dom->get() as $node) {
                $strings[] = (string) $node;
            }

            return $strings;
        };

        // common selectors - * (any), element (type), class & id (hash)
        $dom = new Dom('<div><section id="intro"><h1>h1</h1><h2 class="tagline">h2</h2></section></div>');

        // * (any)
        $this->assertSame([
            '<section id="intro"><h1>h1</h1><h2 class="tagline">h2</h2></section>',
            '<h1>h1</h1>',
            '<h2 class="tagline">h2</h2>'
        ], $domToStrings($dom->find('*')));

        // element (type)
        $this->assertSame([
            '<h1>h1</h1>',
            '<h2 class="tagline">h2</h2>'
        ], $domToStrings($dom->find('h1, h2')));

        // class
        $this->assertSame([
            '<h2 class="tagline">h2</h2>'
        ], $domToStrings($dom->find('.tagline')));

        // id (hash)
        $this->assertSame([
            '<section id="intro"><h1>h1</h1><h2 class="tagline">h2</h2></section>',
        ], $domToStrings($dom->find('#intro')));

        // descendant selectors - E F, E > F
        $dom = new Dom('<div><h2>h2</h2><article><h2>sub</h2><div><h2>inner</h2></div></article></div>');

        // E F
        $this->assertSame(['<h2>sub</h2>', '<h2>inner</h2>'], $domToStrings($dom->find('article h2')));
        $this->assertSame(['<h2>inner</h2>'], $domToStrings($dom->find('div h2')));
        $this->assertSame([], $domToStrings($dom->find('h2 h2')));

        // E > F
        $this->assertSame(['<h2>sub</h2>'], $domToStrings($dom->find('article > h2')));
        $this->assertSame([], $domToStrings($dom->find('h2 > h2')));

        // sibling selectors - E + F, E ~ F
        $dom = new Dom('<div><a href="#">a</a><strong>strong</strong><em>em1</em><em>em2</em><i>i</i></div>');

        // E + F
        $this->assertSame(['<em>em2</em>'], $domToStrings($dom->find('em + em')));
        $this->assertSame(['<strong>strong</strong>'], $domToStrings($dom->find('a + strong')));
        $this->assertSame([], $domToStrings($dom->find('a + em')));

        // E ~ F
        $this->assertSame(['<i>i</i>'], $domToStrings($dom->find('a ~ i')));
        $this->assertSame(['<em>em2</em>'], $domToStrings($dom->find('em ~ em')));
        $this->assertSame(['<em>em1</em>', '<em>em2</em>'], $domToStrings($dom->find('strong ~ em')));
        $this->assertSame([], $domToStrings($dom->find('em ~ strong')));
    }
}