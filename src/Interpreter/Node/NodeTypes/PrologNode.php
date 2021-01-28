<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Node\NodeTypes;

use sFire\Dom\Interpreter\Interfaces\AttributeInterface;
use sFire\Dom\Interpreter\Node\Traits\AttributeTrait;


class PrologNode extends NodeAbstract implements AttributeInterface {

    use AttributeTrait;
}