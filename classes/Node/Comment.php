<?php

namespace SDom\Node;

/**
 * Node representing an HTML comment.
 *
 * Class Comment
 * @package SDom\Node
 */
class Comment implements NodeInterface
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var Element
     */
    protected $parent;

    /**
     * Comment constructor.
     *
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return '<!--' . $this->content . '-->';
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
        if (!$parent instanceof Element) {
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
}