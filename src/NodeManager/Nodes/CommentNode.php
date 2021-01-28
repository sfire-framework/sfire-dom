<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Nodes;

use sFire\Dom\NodeManager\Traits\NoChildrenTrait;

class CommentNode extends NodeAbstract {


    use NoChildrenTrait;


    private CONST TYPE = 'comment';


    /**
     * @inheritdoc
     */
    public function getType(): string {
        return self::TYPE;
    }
}