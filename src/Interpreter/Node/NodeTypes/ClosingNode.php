<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Node\NodeTypes;

use sFire\Dom\Interpreter\Interfaces\TagInterface;
use sFire\Dom\Interpreter\Node\Traits\TagTrait;


class ClosingNode extends NodeAbstract implements TagInterface {

    use TagTrait;
}