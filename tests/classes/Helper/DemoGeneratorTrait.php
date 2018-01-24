<?php

namespace SDom\Test\Helper;

use SDom\Node\CData;
use SDom\Node\Comment;
use SDom\Node\DocType;
use SDom\Node\Element;
use SDom\Node\Text;

/**
 * Trait DemoGeneratorTrait
 * @package SDom\Test\Helper
 */
trait DemoGeneratorTrait
{
    /**
     * @return string
     */
    protected function demoMixedContent(): string
    {
        return 'd"e" &mdash; \'m\'o';
    }

    /**
     * @return CData
     */
    protected function demoCData(): CData
    {
        return new CData($this->demoMixedContent());
    }

    /**
     * @return Comment
     */
    protected function demoComment(): Comment
    {
        return new Comment($this->demoMixedContent());
    }

    /**
     * @return Text
     */
    protected function demoText(): Text
    {
        return new Text($this->demoMixedContent());
    }

    /**
     * @return DocType
     */
    protected function demoDocType(): DocType
    {
        return new DocType('demo');
    }

    protected function demoElement(
        bool $withAttributes = false,
        bool $withChildren = false,
        Element &$childARef = null,
        Element &$childBRef = null,
        Element &$childCRef = null
    ): Element {
        $demo = new Element('demo');

        if ($withAttributes) {
            $demo->setAttribute('a', 'b')
                ->setAttribute('c', 'd')
                ->setAttribute('e', '');
        }

        if ($withChildren) {
            $childARef = new Element('a');
            $childBRef = new Element('b');
            $childCRef = new Element('c');

            $demo->insertAfter($childARef)->insertAfter($childBRef->insertAfter($childCRef));
        }

        return $demo;
    }

    /**
     * @param bool $withAttributes
     * @return Element
     */
    protected function demoVoidElement(bool $withAttributes = false): Element
    {
        $demo = new Element('br');

        if ($withAttributes) {
            $demo->setAttribute('a', 'b')
                ->setAttribute('c', 'd')
                ->setAttribute('e', '');
        }

        return $demo;
    }
}