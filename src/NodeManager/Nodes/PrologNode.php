<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Nodes;

use sFire\Dom\NodeManager\Traits\NoChildrenTrait;

class PrologNode extends NodeAbstract {


    use NoChildrenTrait;


    private CONST TYPE = 'prolog';


    /**
     * @inheritdoc
     */
    public function getType(): string {
        return self::TYPE;
    }
}