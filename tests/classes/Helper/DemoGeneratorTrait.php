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
     * @return CData
     */
    protected function demoCData(): CData
    {
        return new CData('demo');
    }

    /**
     * @return Comment
     */
    protected function demoComment(): Comment
    {
        return new Comment('demo');
    }

    /**
     * @return DocType
     */
    protected function demoDocType(): DocType
    {
        return new DocType('demo');
    }

    /**
     * @return Text
     */
    protected function demoText(): Text
    {
        return new Text('demo');
    }

    /**
     * @param bool $withAttributes
     * @param bool $withChildren
     * @return Element
     */
    protected function demoElement(bool $withAttributes = false, bool $withChildren = false): Element
    {
        $demo = new Element('demo');

        if ($withAttributes) {
            $demo->setAttribute('a', 'b')
                ->setAttribute('c', 'd')
                ->setAttribute('e', '');
        }

        if ($withChildren) {
            $demo->insertAfter(new Element('a'))
                ->insertAfter((new Element('b'))->insertAfter(new Element('c')));
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