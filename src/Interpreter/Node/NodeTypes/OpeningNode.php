<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Node\NodeTypes;

use sFire\Dom\Interpreter\Interfaces\AttributeInterface;
use sFire\Dom\Interpreter\Interfaces\ClosingNodeInterface;
use sFire\Dom\Interpreter\Node\Traits\AttributeTrait;
use sFire\Dom\Interpreter\Interfaces\TagInterface;
use sFire\Dom\Interpreter\Node\Traits\ClosingNodeTrait;
use sFire\Dom\Interpreter\Node\Traits\TagTrait;


class OpeningNode extends NodeAbstract implements AttributeInterface, TagInterface, ClosingNodeInterface {

    use AttributeTrait;
    use TagTrait;
    use ClosingNodeTrait;
}