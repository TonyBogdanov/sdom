<?php

namespace SDom\SelectorMatcher;

use SDom\Node\Element;
use SDom\SelectorMatcher;
use Symfony\Component\CssSelector\Node\ClassNode;
use Symfony\Component\CssSelector\Node\NodeInterface;

/**
 * @pattern E.warning
 * @meaning an E element whose class is "warning" (the document language specifies how class is determined).
 * @link https://www.w3.org/TR/css3-selectors/#class-html
 *
 * Trait ClassNodeTrait
 * @package SDom\SelectorMatcher
 */
trait ClassNodeTrait
{
    /**
     * @param ClassNode $token
     * @param Element $node
     * @param null|Element $effectiveRoot
     * @return bool
     */
    protected function matchClassNode(ClassNode $token, Element $node, Element $effectiveRoot = null): bool
    {
        // attribute "class" must exist
        if (!$node->hasAttribute('class')) {
            return false;
        }

        // attribute "class" must contain the requested class name
        if (!SelectorMatcher::containsWord($token->getName(), $node->getAttribute('class'))) {
            return false;
        }

        return $this->match($token->getSelector(), $node, $effectiveRoot);
    }

    /**
     * @param NodeInterface $token
     * @param Element $node
     * @param Element|null $effectiveRoot
     * @return bool
     */
    abstract public function match(NodeInterface $token, Element $node, Element $effectiveRoot = null): bool;
}