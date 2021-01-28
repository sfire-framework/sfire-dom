<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Node\Traits;

use \sFire\Dom\Interpreter\Attribute\Attribute;


trait AttributeTrait {


    private array $attributes = [];


    public function addAttribute(Attribute $attribute): void {
        $this -> attributes[] = $attribute;
    }

    public function getAttributes(): array {
        return $this -> attributes;
    }
}
