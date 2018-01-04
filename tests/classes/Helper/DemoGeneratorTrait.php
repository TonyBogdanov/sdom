<?php

namespace SDom\Test\Helper;

use SDom\Node\CData;
use SDom\Node\Comment;
use SDom\Node\DocType;
use SDom\Node\Element;
use SDom\Node\Text;

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
     * @return Element
     */
    protected function demoElement(): Element
    {
        return new Element('demo');
    }

    /**
     * @return Element
     */
    protected function demoVoidElement(): Element
    {
        return new Element('br');
    }
}