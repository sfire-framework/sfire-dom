<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Traits;

trait TagTrait {

    private string $tagName = '';

    public function setTagName(string $tagName): void {
        $this -> tagName = $tagName;
    }

    public function getTagName(): string {
        return $this -> tagName;
    }
}


