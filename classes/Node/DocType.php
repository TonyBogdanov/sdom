<?php

namespace SDom\Node;

/**
 * Node representing an HTML document's DocType declaration.
 *
 * Class DocType
 * @package SDom\Node
 */
class DocType implements NodeInterface
{
    /**
     * @var string
     */
    protected $content;

    /**
     * DocType constructor.
     *
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return '<!DOCTYPE ' . $this->content . '>';
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        throw new \BadMethodCallException('Native cloning is not allowed, use clone() instead.');
    }

    /**
     * @inheritDoc
     */
    public function parent(): ?NodeInterface
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function attach(NodeInterface $parent): NodeInterface
    {
        throw new \BadMethodCallException(sprintf(
            'Node of type %s cannot be part of a hierarchy, invoking %s has no effect.',
            get_class($this),
            explode('::', __METHOD__)[1]
        ));
    }

    /**
     * @inheritDoc
     */
    public function detach(): NodeInterface
    {
        throw new \BadMethodCallException(sprintf(
            'Node of type %s cannot be part of a hierarchy, invoking %s has no effect.',
            get_class($this),
            explode('::', __METHOD__)[1]
        ));
    }

    /**
     * @inheritDoc
     */
    public function clone(): NodeInterface
    {
        return new static($this->content);
    }
}