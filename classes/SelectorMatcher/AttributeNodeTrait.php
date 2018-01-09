<?php

namespace SDom\SelectorMatcher;

use SDom\Node\Element;
use SDom\SelectorMatcher;
use Symfony\Component\CssSelector\Node\AttributeNode;

/**
 * @pattern E[foo]
 * @meaning an E element with a "foo" attribute
 * @link https://www.w3.org/TR/css3-selectors/#attribute-selectors
 *
 * @pattern E[foo="bar"]
 * @meaning an E element whose "foo" attribute value is exactly equal to "bar"
 * @link https://www.w3.org/TR/css3-selectors/#attribute-selectors
 *
 * @pattern E[foo~="bar"]
 * @meaning an E element whose "foo" attribute value is a list of whitespace-separated values, one of which is
 * exactly equal to "bar"
 * @link https://www.w3.org/TR/css3-selectors/#attribute-selectors
 *
 * @pattern E[foo^="bar"]
 * @meaning an E element whose "foo" attribute value begins exactly with the string "bar"
 * @link https://www.w3.org/TR/css3-selectors/#attribute-selectors
 *
 * @pattern E[foo$="bar"]
 * @meaning an E element whose "foo" attribute value ends exactly with the string "bar"
 * @link https://www.w3.org/TR/css3-selectors/#attribute-selectors
 *
 * @pattern E[foo*="bar"]
 * @meaning an E element whose "foo" attribute value contains the substring "bar"
 * @link https://www.w3.org/TR/css3-selectors/#attribute-selectors
 *
 * @pattern E[foo|="en"]
 * @meaning an E element whose "foo" attribute has a hyphen-separated list of values beginning (from the left) with "en"
 * @link https://www.w3.org/TR/css3-selectors/#attribute-selectors
 *
 * Trait AttributeNodeTrait
 * @package SDom\SelectorMatcher
 */
trait AttributeNodeTrait
{
    /**
     * @param AttributeNode $token
     * @param Element $node
     * @param null|Element $effectiveRoot
     * @return bool
     */
    protected function matchAttributeNode(AttributeNode $token, Element $node, Element $effectiveRoot = null): bool
    {
        $attribute = $token->getAttribute();

        // node attribute must exist, regardless of operator
        if (!$node->hasAttribute($attribute)) {
            return false;
        }

        $neededValue = $token->getValue();
        $actualValue = $node->getAttribute($attribute);
        $operator = $token->getOperator();

        // if attribute operator is "exists", no further checks are needed
        if ('exists' === $operator) {
            return $this->match($token->getSelector(), $node, $effectiveRoot);
        }

        switch ($token->getOperator()) {
            case '=':
                if ($neededValue !== $actualValue) {
                    return false;
                }
                break;

            case '~=':
                if (!SelectorMatcher::containsWord($neededValue, $actualValue)) {
                    return false;
                }
                break;

            case '^=':
                if ($neededValue !== substr($actualValue, 0, strlen($neededValue))) {
                    return false;
                }
                break;

            case '$=':
                if ($neededValue !== substr($actualValue, -strlen($neededValue))) {
                    return false;
                }
                break;

            case '*=':
                if (false === strpos($actualValue, $neededValue)) {
                    return false;
                }
                break;

            case '|=':
                if (
                    $neededValue !== $actualValue &&
                    $neededValue . '-' !== substr($actualValue, 0, strlen($neededValue . '-'))
                ) {
                    return false;
                }
                break;

            default:
                throw new \RuntimeException(sprintf(
                    'Invalid node attribute operator "%s".',
                    $token->getOperator()
                ));
        }

        return $this->match($token->getSelector(), $node, $effectiveRoot);
    }
}