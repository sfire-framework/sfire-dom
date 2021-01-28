<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Nodes;


class RootNode extends ParentNodeAbstract {


    private CONST TYPE = 'root';


    /**
     * @inheritdoc
     */
    public function getType(): string {
        return self::TYPE;
    }
}