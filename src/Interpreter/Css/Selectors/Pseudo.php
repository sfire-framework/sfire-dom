<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Css\Selectors;

use sFire\Dom\Interpreter\Css\CssInterpreter;


class Pseudo extends SelectorAbstract {


    private ?string $value = null;
    private ?string $type = null;

    private bool $isConsuming = false;

    private int $parenthesesOpenCount = 0;

    public function append(string $character): void {

        $this -> isConsuming = true;

        switch($character) {

            //Quotes
            case CssInterpreter::DOUBLE_QUOTE:
            case CssInterpreter::SINGLE_QUOTE: $this -> processQuotesSign($character); break;

            //Process bracket open sign
            case CssInterpreter::PARENTHESIS_CLOSED:
            case CssInterpreter::PARENTHESIS_OPEN: $this -> processParenthesis($character); break;


            default: $this -> processDefault($character);
        }

        parent::append($character);
    }


    public function isConsuming(): bool {
        return $this -> isConsuming;
    }


    public function getType(): ?string {
        return $this -> type;
    }

    public function getValue(): ?string {
        return $this -> value;
    }


    public function processQuotesSign(string $character): void {

        if($this -> parenthesesOpenCount > 0) {

            $this -> value ??= '';
            $this -> value .= $character;
            return;
        }

        if(null !== $this -> type) {
            $this -> type ??= '';
            $this -> type .= $character;
            return;
        }
    }


    public function processParenthesis(string $character): void {

        if(null === $this -> value && $character === CssInterpreter::PARENTHESIS_OPEN) {

            $this -> isConsuming = true;
            $this -> parenthesesOpenCount++;
            return;
        }

        if(null !== $this -> value && $character === CssInterpreter::PARENTHESIS_CLOSED) {

            $this -> isConsuming = false;
            $this -> parenthesesOpenCount--;
            return;
        }

        $this -> value ??= '';
        $this -> value .= $character;
    }


    public function processDefault(string $character): void {

        if(0 === $this -> parenthesesOpenCount) {

            $this -> type ??= '';
            $this -> type .= $character;
            return;
        }

        $this -> value ??= '';
        $this -> value .= $character;
    }
}