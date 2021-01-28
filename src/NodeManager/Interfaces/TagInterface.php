<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Interfaces;

interface TagInterface {

    public function setTagName(string $tagName): void;
    public function getTagName(): string;
}