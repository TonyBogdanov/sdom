<?php

namespace SDom\Node;

/**
 * Node representing an HTML element.
 *
 * Class Element
 * @package SDom\Node
 */
class Element implements
    NodeInterface,
    \IteratorAggregate,
    \Countable
{
    /**
     * @var string[]
     */
    protected static $void = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'
    ];

    /**
     * @var string
     */
    protected $tag;

    /**
     * @var Element
     */
    protected $parent;

    /**
     * @var string[]
     */
    protected $attributes = [];

    /**
     * @var NodeInterface[]
     */
    protected $children = [];

    /**
     * Element constructor.
     *
     * @param string $tag
     */
    public function __construct(string $tag)
    {
        $this->tag = strtolower($tag);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        $html = '<' . $this->tag;

        /**
         * @var string $key
         * @var string $value
         */
        foreach ($this->attributes as $key => $value) {
            $html .= ' ' . htmlspecialchars($key);

            if ('' !== $value) {
                $html .= '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
            }
        }

        if ($this->isVoid()) {
            $html .= '/>';
        } else {
            $html .= '>';

            foreach ($this->children as $child) {
                $html .= (string) $child;
            }

            $html .= '</' . $this->tag . '>';
        }

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        throw new \BadMethodCallException('Native cloning is not allowed, use clone() instead.');
    }

    /**
     * Retrieve the tag name of the element.
     *
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * Return TRUE if the specified name exists as attribute.
     * The attribute name is lowercased.
     *
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->attributes);
    }

    /**
     * Set the specified value for the specified attribute name.
     * Attributes with no value, or an empty string as value are rendered without the ="..." part.
     * The attribute name is lowercased.
     *
     * @param string $name
     * @param string $value
     * @return Element
     */
    public function setAttribute(string $name, string $value = ''): Element
    {
        $this->attributes[strtolower($name)] = $value;
        return $this;
    }

    /**
     * Retrieve the value of the specified attribute name, or NULL if the attribute does not exist.
     * The attribute name is lowercased.
     *
     * @param string $name
     * @return null|string
     */
    public function getAttribute(string $name): ?string
    {
        if (!$this->hasAttribute($name)) {
            return null;
        }

        return $this->attributes[strtolower($name)];
    }

    /**
     * Remove an attribute with the specified name.
     * The attribute name is lowercased.
     *
     * @param string $name
     * @return Element
     */
    public function removeAttribute(string $name): Element
    {
        if ($this->hasAttribute($name)) {
            unset($this->attributes[strtolower($name)]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function parent(): ?NodeInterface
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    public function attach(NodeInterface $parent): NodeInterface
    {
        if (!$parent instanceof self) {
            throw new \InvalidArgumentException(sprintf(
                'Only node of type %s can be parent to a %s node.',
                Element::class,
                self::class
            ));
        }

        if ($parent->isVoid()) {
            throw new \InvalidArgumentException(sprintf(
                'Node of type %s (void) cannot be parent to a %s node.',
                Element::class,
                self::class
            ));
        }

        if (isset($this->parent)) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function detach(): NodeInterface
    {
        if (isset($this->parent)) {
            $parent = $this->parent;
            $this->parent = null;

            // if detach() is called from a removeChild(), then isChild will fail - disregarding it will cause recursion
            if ($parent->isChild($this)) {
                $parent->removeChild($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function clone(): NodeInterface
    {
        $clone = new static($this->tag);

        /**
         * Inherit attributes.
         *
         * @var string $name
         * @var string $value
         */
        foreach ($this->attributes as $name => $value) {
            $clone->setAttribute($name, $value);
        }

        /**
         * Inherit cloned child nodes.
         *
         * @var int $index
         * @var NodeInterface $child
         */
        foreach ($this->children as $index => $child) {
            $clone->insertAfter($child->clone());
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->children);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->children);
    }

    /**
     * Insert content at the end of the list of child nodes, or after the specified target node.
     * If the target node is not an immediate child node of this one, an exception will be thrown.
     * Attach this node as parent to the inserted node.
     *
     * @param NodeInterface $node
     * @param NodeInterface|null $after
     * @return Element
     */
    public function insertAfter(NodeInterface $node, NodeInterface $after = null): Element
    {
        $index = count($this->children);

        if (isset($after)) {
            $index = array_search($after, $this->children, true);
            if (false === $index) {
                throw new \InvalidArgumentException('Only immediate child nodes can be used as insertAfter anchor.');
            }
        }

        array_splice($this->children, $index + 1, 0, [$node->attach($this)]);
        return $this;
    }

    /**
     * Insert content at the beginning of the list of child nodes, or before the specified target node.
     * If the target node is not an immediate child node of this one, an exception will be thrown.
     * Attach this node as parent to the inserted node.
     *
     * @param NodeInterface $node
     * @param NodeInterface|null $before
     * @return Element
     */
    public function insertBefore(NodeInterface $node, NodeInterface $before = null): Element
    {
        $index = 0;

        if (isset($before)) {
            $index = array_search($before, $this->children, true);
            if (false === $index) {
                throw new \InvalidArgumentException('Only immediate child nodes can be used as insertBefore anchor.');
            }
            $index = (int) $index;
        }

        array_splice($this->children, $index, 0, [$node->attach($this)]);
        return $this;
    }

    /**
     * Returns TRUE if the specified node is an immediate child of the current node.
     *
     * @param NodeInterface $node
     * @return bool
     */
    public function isChild(NodeInterface $node): bool
    {
        return in_array($node, $this->children);
    }

    /**
     * Retrieve a NodeInterface instance (immediate child node) for the specified index.
     * Throw \OutOfBoundsException exception if the specified index is out of bounds.
     *
     * @param int $index
     * @return NodeInterface
     * @throws \OutOfBoundsException
     */
    public function get(int $index): NodeInterface
    {
        $count = count($this);

        if ($index < 0 || $index >= $count) {
            throw new \OutOfBoundsException(sprintf(
                'The requested node index %d is out of the child list bounds [%s].',
                $index,
                0 < $count ? '[0; ' . ($count - 1) . ']' : '(empty child list)'
            ));
        }

        return $this->children[$index];
    }

    /**
     * Retrieve the positional index of the specified NodeInterface in the list of immediate child nodes.
     * If the target node is not an immediate child node of this one, an exception will be thrown.
     *
     * @param NodeInterface $node
     * @return int
     * @throws \InvalidArgumentException
     */
    public function index(NodeInterface $node): int
    {
        $index = array_search($node, $this->children, true);
        if (false === $index) {
            throw new \InvalidArgumentException('The specified node is not an immediate child node.');
        }

        return $index;
    }

    /**
     * Remove the specified node from the list of immediate children of this node.
     * If the target node is not an immediate child node of this one, an exception will be thrown.
     * The node's detach() method will also be called to release the parent reference if such is set.
     *
     * @param NodeInterface $node
     * @return Element
     */
    public function removeChild(NodeInterface $node): Element
    {
        $index = $this->index($node);
        $child = $this->children[$index];

        array_splice($this->children, $index, 1);

        if (null !== $child->parent()) {
            $child->detach();
        }

        return $this;
    }

    /**
     * Remove all child nodes.
     *
     * @return Element
     */
    public function clear(): Element
    {
        /** @var NodeInterface $node */
        foreach ($this as $node) {
            $this->removeChild($node);
        }

        return $this;
    }

    /**
     * Returns TRUE if the element's tag matches the list of void element tags.
     *
     * @return bool
     */
    public function isVoid(): bool
    {
        return in_array($this->tag, static::$void);
    }
}