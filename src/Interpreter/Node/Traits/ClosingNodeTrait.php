<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Node\Traits;


trait ClosingNodeTrait {


    private bool $isSelfClosing = false;


    public function setSelfClosingNode(bool $selfClosing): void {
        $this -> isSelfClosing = $selfClosing;
    }


    public function isSelfClosing(): bool {
        return $this -> isSelfClosing;
    }
}
