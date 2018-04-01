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
use Symfony\Component\CssSelector\Node;
use Symfony\Component\CssSelector\Parser\Parser;

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
     * Singleton instance to a CSS selector parser.
     *
     * @var Parser
     */
    protected static $selectorParser;

    /**
     * Singleton instance to a CSS selector matcher.
     *
     * @var SelectorMatcher
     */
    protected static $selectorMatcher;

    /**
     * Singleton instance to an HTML tokenizer.
     *
     * @var HtmlTokenizer
     */
    protected static $tokenizer;

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
     * Traverse the specified node and all of its child nodes recursively (any node type is accepted, but only element
     * nodes are processed) and match against the specified selector tokens.
     *
     * Add all nodes that match at least one of the selector tokens to the specified Dom collection and return it.
     *
     * The supplied $effectiveRoot node will be considered the root of the tree, even if there are more ancestors.
     * Child nodes will be treated, when matching, as if they don't have a parent node.
     *
     * @param Dom $dom
     * @param array $selectorTokens
     * @param NodeInterface $node
     * @param NodeInterface $effectiveRoot
     * @return Dom
     */
    protected static function traverseMatch(
        Dom $dom,
        array $selectorTokens,
        NodeInterface $node,
        NodeInterface $effectiveRoot
    ): Dom {
        if (!$node instanceof Element) {
            return $dom;
        }

        /** @var Node\NodeInterface $selectorToken */
        foreach ($selectorTokens as $selectorToken) {
            if (self::$selectorMatcher->match($selectorToken, $node, $effectiveRoot)) {
                $dom->add($node);
                break;
            }
        }

        /** @var NodeInterface $childNode */
        foreach ($node as $childNode) {
            static::traverseMatch($dom, $selectorTokens, $childNode, $effectiveRoot);
        }

        return $dom;
    }

    /**
     * Loop over the supplied array of nodes and return the first matched element node or NULL.
     *
     * @param NodeInterface[] $nodes
     * @return null|Element
     */
    protected static function findFirstElement(array $nodes): ?Element
    {
        foreach ($nodes as $node) {
            if ($node instanceof Element) {
                return $node;
            }
        }
        return null;
    }

    /**
     * Loop over the supplied array of nodes and find the first matched element node. If it has child nodes, find the
     * first matched element child, then recurse until the inner-most element node with no children (element nodes)
     * is found and return it.
     *
     * If the array does not contain an element node, return NULL.
     *
     * @param NodeInterface[] $nodes
     * @param null|Element $fallback
     * @return null|Element
     */
    protected static function findFirstInnermostElement(array $nodes, Element $fallback = null): ?Element
    {
        $element = static::findFirstElement($nodes);
        if (!$element) {
            return $fallback;
        }

        if ($element->isVoid() || 0 === count($element)) {
            return $element;
        }

        return static::findFirstInnermostElement($element->getIterator()->getArrayCopy(), $element);
    }

    /**
     * Dom constructor.
     * Create new collection from the specified content.
     *
     * If the content is NULL, create empty collection.
     * If the content is a Dom instance, copy the nodes into the new collection.
     * If the content is a NodeInterface, add it to the collection.
     * If the content is a Token, convert it to the corresponding NodeInterface (tree) and add it to the collection.
     * If the content is a TokenCollection, convert it to NodeInterface instances and add them to the collection.
     * If the content is a string, parse as HTML and treat as TokenCollection.
     *
     * Strings with all whitespace produce empty collections. Strings with invalid HTML throw an exception.
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
                 * Inherit attributes.
                 *
                 * @var string $name
                 * @var string|bool $value
                 */
                foreach ($content->getAttributes() as $name => $value) {
                    // if value is boolean true, that means the attribute is empty
                    $node->setAttribute($name, true === $value ? '' : $value);
                }

                /**
                 * Inherit children.
                 *
                 * @var Tokens\Token $child
                 */
                foreach ($content->getChildren() as $child) {
                    /** @var NodeInterface $childNode */
                    $childNode = (new static($child))->get(0);
                    $node->insertAfter($childNode);
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
                    if (!isset(self::$tokenizer)) {
                        self::$tokenizer = new HtmlTokenizer();
                    }
                    $tokenCollection = self::$tokenizer->parse($content);
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
     * Return the concatenated string representation of all nodes in the collection.
     *
     * @return string
     */
    public function __toString(): string
    {
        $html = '';

        foreach ($this->nodes as $node) {
            $html .= (string) $node;
        }

        return $html;
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
            if (!$node instanceof Element) {
                continue;
            }

            /** @var NodeInterface $child */
            foreach ($node as $child) {
                $dom->add($child);
            }
        }

        return $dom;
    }

    /**
     * Insert content after all immediate child nodes of each Element node in the collection.
     *
     * If any node derived from the content already has a parent node, a cloned copy will be used instead and it will
     * be assigned a new parent node. This means that, if appended to more than one Element node, references to each
     * appended node will only point to the very first insertion.
     *
     * E.g. if the same node is appended to two or more Element nodes, its reference will point to the node with the
     * first Element as parent. Nodes appended to all other Element nodes will be cloned copies. The same rule applies
     * to child nodes of appended nodes, at any depth, as the whole sub-tree is cloned recursively.
     *
     * @param $content
     * @return Dom
     */
    public function append($content): Dom
    {
        $nodes = (new static($content))->nodes;

        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if (!$node instanceof Element) {
                continue;
            }

            /** @var NodeInterface $child */
            foreach ($nodes as $child) {
                $node->insertAfter(null === $child->parent() ? $child : $child->clone());
            }
        }

        return $this;
    }

    /**
     * Insert content before all immediate child nodes of each Element node in the collection.
     *
     * If any node derived from the content already has a parent node, a cloned copy will be used instead and it will
     * be assigned a new parent node. This means that, if prepended to more than one Element node, references to each
     * prepended node will only point to the very first insertion.
     *
     * E.g. if the same node is prepended to two or more Element nodes, its reference will point to the node with the
     * first Element as parent. Nodes prepended to all other Element nodes will be cloned copies. The same rule applies
     * to child nodes of prepended nodes, at any depth, as the whole sub-tree is cloned recursively.
     *
     * If the supplied content resolves to a collection of nodes, they will be prepended as a group, keeping the order.
     *
     * @param $content
     * @return Dom
     */
    public function prepend($content): Dom
    {
        $nodes = array_reverse((new static($content))->nodes);

        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if (!$node instanceof Element) {
                continue;
            }

            /** @var NodeInterface $child */
            foreach ($nodes as $child) {
                $node->insertBefore(null === $child->parent() ? $child : $child->clone());
            }
        }

        return $this;
    }

    /**
     * Insert content before each Element node in the collection. If an element in the collection does not have a
     * parent node it will be skipped / ignored.
     *
     * If any node derived from the content already has a parent node, a cloned copy will be used instead and it will
     * be assigned a new parent node. This means that, if inserted before more than one Element node, references to
     * each inserted node will only point to the very first successful insertion.
     *
     * E.g. if the same node is inserted before two or more Element nodes, its reference will point to the node
     * inserted before the first eligible Element in the collection (an Element without a parent node is not eligible
     * and will be ignored). Nodes inserted before all other Element nodes will be cloned copies. The same rule applies
     * to child nodes of inserted nodes, at any depth, as the whole sub-tree is cloned recursively.
     *
     * @param $content
     * @return Dom
     */
    public function before($content): Dom
    {
        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if (!$node instanceof Element || null === $node->parent()) {
                continue;
            }

            /** @var Element $parent */
            $parent = $node->parent();

            /** @var NodeInterface $child */
            foreach (array_reverse((new static($content))->nodes) as $child) {
                $parent->insertBefore(null === $child->parent() ? $child : $child->clone(), $node);
            }
        }

        return $this;
    }

    /**
     * Insert content after each Element node in the collection. If an element in the collection does not have a
     * parent node it will be skipped / ignored.
     *
     * If any node derived from the content already has a parent node, a cloned copy will be used instead and it will
     * be assigned a new parent node. This means that, if inserted after more than one Element node, references to
     * each inserted node will only point to the very first successful insertion.
     *
     * E.g. if the same node is inserted after two or more Element nodes, its reference will point to the node
     * inserted after the first eligible Element in the collection (an Element without a parent node is not eligible
     * and will be ignored). Nodes inserted after all other Element nodes will be cloned copies. The same rule applies
     * to child nodes of inserted nodes, at any depth, as the whole sub-tree is cloned recursively.
     *
     * @param $content
     * @return Dom
     */
    public function after($content): Dom
    {
        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if (!$node instanceof Element || null === $node->parent()) {
                continue;
            }

            /** @var Element $parent */
            $parent = $node->parent();

            /** @var NodeInterface $child */
            foreach (array_reverse((new static($content))->nodes) as $child) {
                $parent->insertAfter(null === $child->parent() ? $child : $child->clone(), $node);
            }
        }

        return $this;
    }

    /**
     * Wrap a clone of the supplied content around each node with a parent (not only element nodes) in the collection.
     *
     * If the content resolves to a collection of more than one wrapping element node, use only the first one.
     * If the wrapping element has children use a single-element-per-level sub-tree, where the wrapping element is
     * the root and may contain one and only one element node, which may contain another one and only one element node
     * and so on.
     *
     * Wrap the current collection nodes in clones of this sub-tree as immediate children of the inner-most element
     * node of the tree. If a node in the collection does not have a parent element, ignore it.
     *
     * If the wrapping collection does not contain at least one element node, do nothing.
     *
     * Return the original collection for chaining.
     *
     * @param $content
     * @return Dom
     */
    public function wrap($content): Dom
    {
        // get a detached clone of the first element node in the content
        /** @var Element $wrapper */
        $wrapper = static::findFirstElement((new static($content))->nodes);
        if (!$wrapper) {
            return $this;
        }
        $wrapper = $wrapper->clone()->detach();

        // collapse the wrapper down to a single-element-per-level tree of clones
        $inner = $wrapper;
        while (0 < count($inner)) {
            $element = static::findFirstElement($inner->getIterator()->getArrayCopy());
            if (!$element) {
                break;
            }
            $inner->clear()->insertAfter($inner = $element->clone());
        }

        // wrap nodes
        foreach ($this->nodes as $node) {
            if (null === $node->parent()) {
                continue;
            }

            /** @var Element $insert */
            $insert = $wrapper->clone();

            /** @var Element $parent */
            $parent = $node->parent();
            $parent->insertAfter($insert, $node);

            $inner = static::findFirstInnermostElement($insert->getIterator()->getArrayCopy());

            if ($inner) {
                $inner->insertAfter($node);
            } else {
                $insert->insertAfter($node);
            }
        }

        return $this;
    }

    /**
     * Wrap a clone of the supplied content around each child node (not only element nodes) of nodes in the collection.
     *
     * This function works exactly like wrap() except it wraps the children of the nodes in the collection instead.
     *
     * Return the original collection for chaining.
     *
     * @param $content
     * @return Dom
     */
    public function wrapInner($content): Dom
    {
        foreach ($this->children() as $child) {
            $child->wrap($content);
        }
        return $this;
    }

    /**
     * Return a new Dom collection of all the descendants of each Element node in the current collection,
     * filtered by the specified CSS selector.
     *
     * @param string $selector
     * @return Dom
     */
    public function find(string $selector): Dom
    {
        if (!isset(self::$selectorParser)) {
            self::$selectorParser = new Parser();
        }

        if (!isset(self::$selectorMatcher)) {
            self::$selectorMatcher = new SelectorMatcher();
        }

        $dom = new static();
        $selectorTokens = self::$selectorParser->parse($selector);

        foreach ($this->get() as $rootNode) {
            /** @var NodeInterface $childNode */
            foreach ($rootNode as $childNode) {
                self::traverseMatch($dom, $selectorTokens, $childNode, $rootNode);
            }
        }

        return $dom;
    }

    /**
     * Adds the specified class(es) to each Element node in the collection.
     *
     * @param string $className
     * @return Dom
     */
    public function addClass(string $className): Dom
    {
        $className = trim($className);

        // bail if no classes to add
        if ('' === $className) {
            return $this;
        }

        $addClasses = preg_split('/\s+/', $className);

        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if (!$node instanceof Element) {
                continue;
            }

            // if the node already has a "class" attribute, merge all classes & make sure the result is unique
            if ($node->hasAttribute('class')) {
                $currentClassName = trim($node->getAttribute('class'));
                $currentClasses = '' === $currentClassName ? [] : preg_split('/\s+/', $currentClassName);
                $node->setAttribute('class', implode(' ', array_unique(array_merge($currentClasses, $addClasses))));
            }

            // if the node does not have a "class" attribute, directly set the new ones
            else {
                $node->setAttribute('class', implode(' ', $addClasses));
            }
        }

        return $this;
    }

    /**
     * Remove a single class, multiple classes, or all classes from each Element node in the collection.
     *
     * @param string|null $className
     * @return Dom
     */
    public function removeClass(string $className = null): Dom
    {
        // if class to remove isn't set, remove all classes, but keep the "class" attribute present
        if (!isset($className)) {
            /** @var NodeInterface $node */
            foreach ($this->nodes as $node) {
                if (!$node instanceof Element || !$node->hasAttribute('class')) {
                    continue;
                }

                $node->setAttribute('class', '');
            }

            return $this;
        }

        $removeClasses = preg_split('/\s+/', $className);

        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if (!$node instanceof Element || !$node->hasAttribute('class')) {
                continue;
            }

            // set to the difference between the current classes and the remove ones
            $currentClasses = preg_split('/\s+/', $node->getAttribute('class'));
            $node->setAttribute('class', implode(' ', array_diff($currentClasses, $removeClasses)));
        }

        return $this;
    }

    /**
     * Return TRUE if at least one of the Element nodes in the collection has the specified class assigned.
     *
     * @param string $className
     * @return bool
     */
    public function hasClass(string $className): bool
    {
        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if (!$node instanceof Element || !$node->hasAttribute('class')) {
                continue;
            }

            if (SelectorMatcher::containsWord($className, $node->getAttribute('class'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the value of an attribute for the first Element node in the collection.
     * Set the value of an attribute for each Element node in the collection.
     *
     * @param string $name
     * @param string|null $value
     * @return string|null|$this
     */
    public function attr(string $name, string $value = null)
    {
        if (isset($value)) {
            /** @var NodeInterface $node */
            foreach ($this->nodes as $node) {
                if (!$node instanceof Element) {
                    continue;
                }

                $node->setAttribute($name, $value);
            }

            return $this;
        }

        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if (!$node instanceof Element) {
                continue;
            }

            return $node->getAttribute($name);
        }

        return null;
    }

    /**
     * Remove an attribute from each Element node in the collection.
     *
     * @param string $name
     * @return Dom
     */
    public function removeAttr(string $name): Dom
    {
        /** @var NodeInterface $node */
        foreach ($this->nodes as $node) {
            if (!$node instanceof Element) {
                continue;
            }

            $node->removeAttribute($name);
        }

        return $this;
    }

    /**
     * Get the combined text contents of each Element node in the collection, including their descendants.
     * Set the content of each Element node in the collection to the specified text.
     *
     * @param string|null $text
     * @return $this|string
     */
    public function text(string $text = null)
    {
        if (isset($text)) {
            foreach ($this->nodes as $node) {
                if (!$node instanceof Element) {
                    continue;
                }

                $node->clear()->insertAfter(new Text($text));
            }

            return $this;
        }

        $text = '';

        foreach ($this->nodes as $node) {
            if ($node instanceof Text) {
                $text .= (string) $node;
            } else if ($node instanceof Element) {
                /** @var Dom $dom */
                foreach ((new static($node))->children() as $dom) {
                    $text .= $dom->text();
                }
            }
        }

        return $text;
    }

    /**
     * Get the HTML contents of the first Element node in the collection.
     * Set the HTML contents of each Element node in the collection.
     *
     * @param string|null $html
     * @return $this|string
     */
    public function html(string $html = null)
    {
        if (isset($html)) {
            foreach ($this->nodes as $node) {
                if (!$node instanceof Element) {
                    continue;
                }

                $node->clear();

                foreach ((new static($html))->nodes as $newNode) {
                    $node->insertAfter($newNode);
                }
            }

            return $this;
        }

        foreach ($this->nodes as $node) {
            if (!$node instanceof Element) {
                continue;
            }

            $html = '';

            /** @var NodeInterface $childNode */
            foreach ($node as $childNode) {
                $html .= (string) $childNode;
            }

            return $html;
        }

        return '';
    }
}