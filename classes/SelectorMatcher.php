<?php

namespace SDom;

use SDom\Node as Dom;
use SDom\SelectorMatcher\AttributeNodeTrait;
use SDom\SelectorMatcher\ClassNodeTrait;
use SDom\SelectorMatcher\CombinedSelectorNodeTrait;
use SDom\SelectorMatcher\ElementNodeTrait;
use SDom\SelectorMatcher\HashNodeTrait;
use Symfony\Component\CssSelector\Node as Css;

/**
 * A class for matching nodes against selector tokens.
 *
 * Class SelectorMatcher
 * @package SDom
 */
class SelectorMatcher
{
    use ElementNodeTrait;
    use AttributeNodeTrait;
    use ClassNodeTrait;
    use HashNodeTrait;
    use CombinedSelectorNodeTrait;

    /**
     * Check if the supplied word is found in the supplied sentence.
     *
     * E.g. the sentence begins with the word followed by whitespace, ends with the word preceded with whitespace,
     * contains the word surrounded by whitespace or is equal to the word.
     *
     * The word itself may not contain whitespace.
     *
     * @param string $word
     * @param string $sentence
     * @return bool
     */
    public static function containsWord(string $word, string $sentence): bool
    {
        return in_array($word, preg_split('/\s+/', $sentence));
    }

    /**
     * Match the supplied CSS token against the supplied Element node and return TRUE if it is matched.
     *
     * The $effectiveRoot specifies an Element node part of the hierarchy that is to be considered as root of the tree.
     * Immediate child nodes will be treated as if they don't have a parent.
     *
     * @param Css\NodeInterface $token
     * @param Dom\Element $node
     * @param Dom\Element|null $effectiveRoot
     * @return bool
     */
    public function match(Css\NodeInterface $token, Dom\Element $node, Dom\Element $effectiveRoot = null): bool
    {
        switch (true) {
            case $token instanceof Css\SelectorNode:
                return $this->match($token->getTree(), $node, $effectiveRoot);

            case $token instanceof Css\ElementNode:
                return $this->matchElementNode($token, $node);

            case $token instanceof Css\AttributeNode:
                return $this->matchAttributeNode($token, $node, $effectiveRoot);

            case $token instanceof Css\ClassNode:
                return $this->matchClassNode($token, $node, $effectiveRoot);

            case $token instanceof Css\HashNode:
                return $this->matchHashNode($token, $node, $effectiveRoot);

            case $token instanceof Css\CombinedSelectorNode:
                return $this->matchCombinedSelectorNode($token, $node, $effectiveRoot);

            default:
                throw new \RuntimeException(sprintf(
                    'Selector token %s is not supported yet.',
                    get_class($token)
                ));
        }
    }
}