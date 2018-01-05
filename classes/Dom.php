<?php

namespace SDom;

use Kevintweber\HtmlTokenizer\HtmlTokenizer;
use Kevintweber\HtmlTokenizer\Tokens;
use SDom\Node\CData;
use SDom\Node\Comment;
use SDom\Node\DocType;
use SDom\Node\Element;
use SDom\Node\NodeInterface;
use SDom\Node\Text;

/**
 * A class to represent a DOM structure - a collection of nodes.
 *
 * Class Dom
 * @package SDom
 */
class Dom implements
    \IteratorAggregate,
    \Countable
{
    /**
     * Collection of nodes.
     *
     * @var NodeInterface[]
     */
    protected $nodes;

    /**
     * Create invalid content exception for the specified content.
     *
     * @param $content
     * @return \InvalidArgumentException
     */
    protected static function createInvalidContentException($content): \InvalidArgumentException
    {
        if (is_object($content)) {
            $detail = '(object) ' . get_class($content);
        } else {
            $detail = '(' . gettype($content) . ')';

            if (is_string($content)) {
                // ignore testing long strings, e.g. any string is sufficient
                // @codeCoverageIgnoreStart
                $detail .= ' “' . (30 < strlen($content) ? substr($content, 0, 30) . '…' : $content) . '“';
                // @codeCoverageIgnoreEnd
            } else if (is_scalar($content)) {
                $detail .= ' ' . var_export($content, true);
            }
        }

        return new \InvalidArgumentException(sprintf(
            'Cannot create node collection from invalid or unsupported content: %s',
            $detail
        ));
    }

    /**
     * Dom constructor.
     * Create new collection from the specified content.
     *
     * If the content is NULL, create empty collection.
     * If the content is a Dom instance, copy the nodes into the new collection.
     * If the content is a NodeInterface, add it to the collection.
     * If the content is a Token, convert it to NodeInterface and add it to the collection.
     * If the content is a TokenCollection, convert it to NodeInterface instances and add them to the collection.
     * If the content is a string, parse as HTML and treat as TokenCollection.
     *
     * @param mixed $content
     */
    public function __construct($content = null)
    {
        switch (true) {
            case !isset($content):
                $this->nodes = [];
                break;

            case $content instanceof self:
                $this->nodes = $content->nodes;
                break;

            case $content instanceof NodeInterface:
                $this->nodes = [$content];
                break;

            case $content instanceof Tokens\CData:
                $this->nodes = [new CData($content->getValue())];
                break;

            case $content instanceof Tokens\Comment:
                $this->nodes = [new Comment($content->getValue())];
                break;

            case $content instanceof Tokens\DocType:
                $this->nodes = [new DocType($content->getValue())];
                break;

            case $content instanceof Tokens\Text:
                $this->nodes = [new Text($content->getValue())];
                break;

            case $content instanceof Tokens\Element:
                $node = new Element($content->getName());

                /**
                 * @var string $name
                 * @var string|bool $value
                 */
                foreach ($content->getAttributes() as $name => $value) {
                    // if value is boolean true, that means the attribute is empty
                    $node->setAttribute($name, true === $value ? '' : $value);
                }

                $this->nodes = [$node];
                break;

            case $content instanceof Tokens\TokenCollection:
                $this->nodes = [];

                /** @var Tokens\Token $token */
                foreach ($content as $token) {
                    $this->add($token);
                }
                break;

            case is_string($content):
                $this->nodes = [];

                try {
                    $tokenCollection = (new HtmlTokenizer())->parse($content);
                } catch (\Exception $e) {
                    throw static::createInvalidContentException($content);
                }

                /** @var Tokens\Token $token */
                foreach ($tokenCollection as $token) {
                    $this->add($token);
                }
                break;

            default:
                throw static::createInvalidContentException($content);
        }
    }

    /**
     * Return an \ArrayIterator with all nodes wrapped in a Dom instance.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator(array_map(function (NodeInterface $node) {
            return new static($node);
        }, $this->nodes));
    }

    /**
     * Return the number of wrapped nodes.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * Retrieve a NodeInterface instance for the specified index, or an array of all NodeInterface instances if index
     * is not specified.
     *
     * Throw \OutOfBoundsException exception if the specified index is out of bounds.
     *
     * @param int|null $index
     * @return NodeInterface|NodeInterface[]
     */
    public function get(int $index = null)
    {
        if (!isset($index)) {
            return $this->nodes;
        }

        $count = count($this);

        if ($index < 0 || $index >= $count) {
            throw new \OutOfBoundsException(sprintf(
                'The requested node index %d is out of the collection bounds [%s].',
                $index,
                0 < $count ? '[0; ' . ($count - 1) . ']' : '(empty collection)'
            ));
        }

        return $this->nodes[$index];
    }

    /**
     * Add the specified content to the end of the collection.
     *
     * @param $content
     * @return Dom
     */
    public function add($content): Dom
    {
        /** @var NodeInterface $node */
        foreach ((new static($content))->nodes as $node) {
            if (!in_array($node, $this->nodes, true)) {
                $this->nodes[] = $node;
            }
        }

        return $this;
    }

    /**
     * Clear the collection.
     *
     * @return Dom
     */
    public function clear(): Dom
    {
        $this->nodes = [];
        return $this;
    }

    /**
     * Retrieve a new Dom collection where the set of matched elements is reduced to the one at the specified index.
     * If the specified index is out of bounds an empty collection is returned.
     *
     * @param int $index
     * @return Dom
     */
    public function eq(int $index): Dom
    {
        return array_key_exists($index, $this->nodes) ?
            new static($this->nodes[$index]) :
            new static();
    }

    /**
     * Retrieve a new Dom collection where the set of matched elements is reduced to the first in the set.
     * If the collection is empty a new empty collection is returned.
     *
     * @return Dom
     */
    public function first(): Dom
    {
        return $this->eq(0);
    }

    /**
     * Retrieve a new Dom collection where the set of matched elements is reduced to the last in the set.
     * If the collection is empty a new empty collection is returned.
     *
     * @return Dom
     */
    public function last(): Dom
    {
        return $this->eq(count($this) - 1);
    }

    /**
     * Get a new Dom collection with the immediate child nodes of each of the Element nodes in the collection.
     *
     * @return Dom
     */
    public function children(): Dom
    {
        $dom = new Dom();

        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if ($node instanceof Element) {
                /** @var NodeInterface $child */
                foreach ($node as $child) {
                    $dom->add($child);
                }
            }
        }

        return $dom;
    }

    /**
     * Insert content, specified by the parameter, to the end of immediate child nodes of all Element nodes in the
     * collection.
     *
     * @param $content
     * @return Dom
     */
    public function append($content): Dom
    {
        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if ($node instanceof Element) {
                /** @var NodeInterface $child */
                foreach ((new static($content))->nodes as $child) {
                    $node->insertAfter(null === $child->parent() ? $child : clone $child);
                }
            }
        }

        return $this;
    }

    /**
     * Insert content, specified by the parameter, to the beginning of immediate child nodes of all Element nodes in
     * the collection.
     *
     * @param $content
     * @return Dom
     */
    public function prepend($content): Dom
    {
        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if ($node instanceof Element) {
                /** @var NodeInterface $child */
                foreach (array_reverse((new static($content))->nodes) as $child) {
                    $node->insertBefore($child);
                }
            }
        }

        return $this;
    }
}