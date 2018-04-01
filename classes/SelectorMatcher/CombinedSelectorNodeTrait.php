<?php

namespace SDom\SelectorMatcher;

use SDom\Node\Element;
use Symfony\Component\CssSelector\Node\CombinedSelectorNode;

/**
 * @pattern E F
 * @meaning an F element descendant of an E element
 * @link https://www.w3.org/TR/css3-selectors/#descendant-combinators
 *
 * @pattern E > F
 * @meaning an F element child of an E element
 * @link https://www.w3.org/TR/css3-selectors/#child-combinators
 *
 * @pattern E + F
 * @meaning an F element immediately preceded by an E element
 * @link https://www.w3.org/TR/css3-selectors/#adjacent-sibling-combinators
 *
 * @pattern E ~ F
 * @meaning an F element preceded by an E element
 * @link https://www.w3.org/TR/css3-selectors/#general-sibling-combinators
 *
 * Trait ClassNodeTrait
 * @package SDom\SelectorMatcher
 */
trait CombinedSelectorNodeTrait
{
    /**
     * @param CombinedSelectorNode $token
     * @param Element $node
     * @param null|Element $effectiveRoot
     * @return bool
     */
    protected function matchDescendantCombinedSelectorNode(
        CombinedSelectorNode $token,
        Element $node,
        Element $effectiveRoot = null
    ): bool {
        // node must have a non-root parent
        if (null === $node->parent() || $effectiveRoot === $node->parent()) {
            return false;
        }

        // node must match the sub-selector
        if (!$this->match($token->getSubSelector(), $node, $effectiveRoot)) {
            return false;
        }

        $parent = $node;

        // node must have a parent that matches the selector, anywhere up the chain
        do {
            /** @var Element $parent */
            $parent = $parent->parent();

            if ($this->match($token->getSelector(), $parent, $effectiveRoot)) {
                return true;
            }
        } while (null !== $parent->parent() && $effectiveRoot !== $parent->parent());

        return false;
    }

    /**
     * @param CombinedSelectorNode $token
     * @param Element $node
     * @param null|Element $effectiveRoot
     * @return bool
     */
    protected function matchChildCombinedSelectorNode(
        CombinedSelectorNode $token,
        Element $node,
        Element $effectiveRoot = null
    ): bool {
        // node must have a non-root parent
        if (null === $node->parent() || $effectiveRoot === $node->parent()) {
            return false;
        }

        /** @var Element $parent */
        $parent = $node->parent();

        // node must match the sub-selector
        if (!$this->match($token->getSubSelector(), $node, $effectiveRoot)) {
            return false;
        }

        // node's parent must match the selector
        return $this->match($token->getSelector(), $parent, $effectiveRoot);
    }

    /**
     * @param CombinedSelectorNode $token
     * @param Element $node
     * @param null|Element $effectiveRoot
     * @return bool
     */
    protected function matchAdjacentCombinedSelectorNode(
        CombinedSelectorNode $token,
        Element $node,
        Element $effectiveRoot = null
    ): bool {
        // node must have a parent in order to determine position (index), parent CAN be the effective root
        if (null === $node->parent()) {
            return false;
        }

        /** @var Element $parent */
        $parent = $node->parent();

        // node must have an immediately preceding sibling that matches the selector
        // don't bother if the node is the first child (no siblings on the left)
        // ignored \InvalidArgumentException as $node is always a child of $parent
        $index = $parent->index($node);
        if (0 === $index) {
            return false;
        }

        // don't bother if the sibling is not an Element node
        // ignored \OutOfBoundsException as $index will always be within the list of children
        $sibling = $parent->get($index - 1);
        if (!$sibling instanceof Element) {
            return false;
        }

        // match the selector
        return $this->match($token->getSelector(), $sibling, $effectiveRoot);
    }

    /**
     * @param CombinedSelectorNode $token
     * @param Element $node
     * @param null|Element $effectiveRoot
     * @return bool
     */
    protected function matchGeneralSiblingCombinedSelectorNode(
        CombinedSelectorNode $token,
        Element $node,
        Element $effectiveRoot = null
    ): bool {
        // node must have a parent in order to determine position (index), parent CAN be the effective root
        if (null === $node->parent()) {
            return false;
        }

        /** @var Element $parent */
        $parent = $node->parent();

        // node must have a preceding sibling (may not be immediate) that matches the selector
        // don't bother if the node is the first child (no siblings on the left)
        // ignored \InvalidArgumentException as $node is always a child of $parent
        $index = $parent->index($node);
        if (0 === $index) {
            return false;
        }

        // test all preceding siblings & bail after the first successful match
        for ($i = $index - 1; $i >= 0; $i--) {
            // skip the sibling if it's not an Element node
            // ignored \OutOfBoundsException as $index will always be within the list of children
            $sibling = $parent->get($i);
            if (!$sibling instanceof Element) {
                continue;
            }

            // match the selector
            if ($this->match($token->getSelector(), $sibling, $effectiveRoot)) {
                return true;
            }
        }

        // no sibling matches the selector
        return false;
    }

    /**
     * @param CombinedSelectorNode $token
     * @param Element $node
     * @param null|Element $effectiveRoot
     * @return bool
     */
    protected function matchCombinedSelectorNode(
        CombinedSelectorNode $token,
        Element $node,
        Element $effectiveRoot = null
    ): bool {
        // node must match the sub selector
        if (!$this->match($token->getSubSelector(), $node, $effectiveRoot)) {
            return false;
        }

        switch ($token->getCombinator()) {
            case ' ':
                return $this->matchDescendantCombinedSelectorNode($token, $node, $effectiveRoot);

            case '>':
                return $this->matchChildCombinedSelectorNode($token, $node, $effectiveRoot);

            case '+':
                return $this->matchAdjacentCombinedSelectorNode($token, $node, $effectiveRoot);

            case '~':
                return $this->matchGeneralSiblingCombinedSelectorNode($token, $node, $effectiveRoot);

            default:
                throw new \RuntimeException(sprintf(
                    'Invalid combined selector combinator "%s".',
                    $token->getCombinator()
                ));
        }
    }
}