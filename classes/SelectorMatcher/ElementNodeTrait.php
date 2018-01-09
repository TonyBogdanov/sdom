<?php

namespace SDom\SelectorMatcher;

use SDom\Node\Element;
use Symfony\Component\CssSelector\Node\ElementNode;

/**
 * @pattern *
 * @meaning any element
 * @link https://www.w3.org/TR/css3-selectors/#universal-selector
 *
 * @pattern E
 * @meaning an element of type E
 * @link https://www.w3.org/TR/css3-selectors/#type-selectors
 *
 * Trait ElementNodeTrait
 * @package SDom\SelectorMatcher
 */
trait ElementNodeTrait
{
    /**
     * @param ElementNode $token
     * @param Element $node
     * @param null|Element $effectiveRoot
     * @return bool
     */
    protected function matchElementNode(ElementNode $token, Element $node, Element $effectiveRoot = null): bool
    {
        // target element tag name may be null, directly return true as ElementNode tokens have no sub-selectors
        if (null === $token->getElement()) {
            return true;
        }

        // node tag name must match
        return $node->getTag() === $token->getElement();
    }
}