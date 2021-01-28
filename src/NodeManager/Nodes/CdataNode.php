<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Nodes;

use sFire\Dom\NodeManager\Traits\NoChildrenTrait;

class CdataNode extends NodeAbstract {


    use NoChildrenTrait;


    private CONST TYPE = 'cdata';


    /**
     * @inheritdoc
     */
    public function getType(): string {
        return self::TYPE;
    }
}