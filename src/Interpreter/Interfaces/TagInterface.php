<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Interfaces;


interface TagInterface {


    public function appendTagName(string $character): self;


    public function getTagName(): ?string;
}