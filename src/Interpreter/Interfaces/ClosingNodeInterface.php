<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Interfaces;


interface ClosingNodeInterface {


    public function setSelfClosingNode(bool $selfClosing): void;


    public function isSelfClosing(): bool;
}