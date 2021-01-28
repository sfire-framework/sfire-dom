<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Interfaces;

interface ChildrenBehaviorInterface {
    public function canHaveChildren(): bool;
}