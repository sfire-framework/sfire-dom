<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Nodes;

use sFire\Dom\NodeManager\Interfaces\TagInterface;
use sFire\Dom\NodeManager\Traits\NoChildrenTrait;
use sFire\Dom\NodeManager\Traits\TagTrait;

class ClosingNode extends NodeAbstract implements TagInterface {


    use NoChildrenTrait;
    use TagTrait;


    private CONST TYPE = 'closing';


    /**
     * @inheritdoc
     */
    public function getType(): string {
        return self::TYPE;
    }
}