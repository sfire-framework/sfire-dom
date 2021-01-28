<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Node\Traits;


trait TagTrait {


    private ?string $tagName = null;


    public function appendTagName(string $character): self {

        $this -> tagName ??= '';
        $this -> tagName .= $character;

        return $this;
    }


    public function getTagName(): ?string {
        return $this -> tagName;
    }
}
