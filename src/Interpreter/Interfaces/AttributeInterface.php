<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Interfaces;

use \sFire\Dom\Interpreter\Attribute\Attribute;


interface AttributeInterface {


    public function getAttributes(): array;


    public function addAttribute(Attribute $attribute): void;
}