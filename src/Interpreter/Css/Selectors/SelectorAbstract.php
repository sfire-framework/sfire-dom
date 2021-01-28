<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Css\Selectors;


abstract class SelectorAbstract {


    protected ?string $content = null;


    public function append(string $character): void {

        $this -> content ??= '';
        $this -> content .= $character;
    }


    public function getContent(): ?string {
        return $this -> content;
    }
}