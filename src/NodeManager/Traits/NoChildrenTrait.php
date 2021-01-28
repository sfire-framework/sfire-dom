<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Traits;

trait NoChildrenTrait {

    public function canHaveChildren(): bool {
        return false;
    }
}