<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Nodes;

use sFire\Dom\NodeManager\Traits\NoChildrenTrait;


class TextNode extends NodeAbstract {


    use NoChildrenTrait;


    private CONST TYPE = 'text';


    /**
     * @inheritdoc
     */
    public function getType(): string {
        return self::TYPE;
    }
}