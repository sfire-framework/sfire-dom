<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Node\NodeTypes;


abstract class NodeAbstract  {


    /**
     * Contains the content for the element
     * @var string|null
     */
    private ?string $content = null;


    /**
     * Appends a given character to the current content of the element
     * @param string $character The character to append to the current content of the element
     * @return self
     */
    public function appendContent(string $character): self {

        $this -> content ??= '';
        $this -> content .= $character;

        return $this;
    }


    /**
     * Returns the content
     * @return string|null
     */
    public function getContent(): ?string {
        return $this -> content;
    }
}