<?php

namespace SDom\Node;

/**
 * Interface to represent a node.
 *
 * Interface NodeInterface
 * @package SDom\Node
 */
interface NodeInterface
{
    /**
     * Return the string (HTML) representation of the node.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Throw exception to signify native cloning is not allowed.
     * One should use clone() instead.
     *
     * @throws \BadMethodCallException
     */
    public function __clone();

    /**
     * Return the parent node or null if such isn't set.
     *
     * @return null|NodeInterface
     */
    public function parent(): ?NodeInterface;

    /**
     * Attach this node to another one making the later a parent.
     *
     * When invoking this method you will also need to manually make sure that this node is present as a child node
     * of the supplied parent when applicable to ensure a proper bidirectional relationship.
     *
     * If this node is already attached to a different parent node, it will be removed as a child node from the current
     * parent before attaching to the new one when applicable.
     *
     * @param NodeInterface $parent
     * @return $this
     */
    public function attach(NodeInterface $parent): NodeInterface;

    /**
     * Detach this node from it's parent, if such is set by both removing the parent node reference, and removing this
     * node as a child node from the parent when applicable.
     *
     * @return $this
     */
    public function detach(): NodeInterface;

    /**
     * Ensure child nodes are also cloned when applicable.
     * Ensure old node is detached from parent.
     * Ensure (new) cloned node is attached to parent node.
     *
     * @return NodeInterface
     */
    public function clone(): NodeInterface;
}