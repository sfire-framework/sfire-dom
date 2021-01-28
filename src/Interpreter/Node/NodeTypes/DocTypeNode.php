<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Node\NodeTypes;

use sFire\Dom\Interpreter\Interfaces\AttributeInterface;
use sFire\Dom\Interpreter\Interfaces\ClosingNodeInterface;
use sFire\Dom\Interpreter\Node\Traits\AttributeTrait;
use sFire\Dom\Interpreter\Node\Traits\ClosingNodeTrait;


class DocTypeNode extends NodeAbstract implements AttributeInterface, ClosingNodeInterface {

    use AttributeTrait;
    use ClosingNodeTrait;
}