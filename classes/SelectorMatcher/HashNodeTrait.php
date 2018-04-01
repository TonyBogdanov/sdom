<?php

namespace SDom\SelectorMatcher;

use SDom\Node\Element;
use SDom\Node\NodeInterface;
use Symfony\Component\CssSelector\Node\HashNode;

/**
 * @pattern E#myid
 * @meaning an E element with ID equal to "myid".
 * @link https://www.w3.org/TR/css3-selectors/#id-selectors
 *
 * Trait HashNodeTrait
 * @package SDom\SelectorMatcher
 */
trait HashNodeTrait
{
    /**
     * @param HashNode $token
     * @param Element $node
     * @param null|Element $effectiveRoot
     * @return bool
     */
    protected function matchHashNode(HashNode $token, Element $node, Element $effectiveRoot = null): bool
    {
        // attribute "id" must exist
        if (!$node->hasAttribute('id')) {
            return false;
        }

        // attribute "id" must be identical to the requested ID
        if ($token->getId() !== $node->getAttribute('id')) {
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