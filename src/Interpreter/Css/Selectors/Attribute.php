<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Css\Selectors;

use sFire\Dom\Interpreter\Css\CssInterpreter;


class Attribute extends SelectorAbstract {


    private ?string $enclosureType = null;


    /**
     * Contains the attribute operator
     * @var null|string
     */
    private ?string $operator = null;


    /**
     * Contains the attribute type
     * @var null|string
     */
    private ?string $type = null;


    /**
     * Contains the attribute directive
     * @var null|string
     */
    private ?string $directive = null;


    /**
     *
     * @var bool
     */
    private bool $caseInsensitive = true;


    /**
     * Contains the attribute value
     * @var null|string
     */
    private ?string $value = null;


    private bool $isConsuming = false;

    private int $enclosureCount = 0;

    private int $bracketCount = 0;


    public function append(string $character): void {

        $this -> isConsuming = true;

        switch($character) {

            //Quotes
            case CssInterpreter::DOUBLE_QUOTE:
            case CssInterpreter::SINGLE_QUOTE: $this -> processQuotesSign($character); break;

            //Process attribute operator signs
            case CssInterpreter::EQUAL:
            case CssInterpreter::PIPE:
            case CssInterpreter::POWER:
            case CssInterpreter::DOLLAR: $this -> processOperatorSign($character); break;

            //Colon sign
            case CssInterpreter::COLON: $this -> processColonSign(); break;

            //Process bracket open sign
            case CssInterpreter::BRACKET_CLOSED:
            case CssInterpreter::BRACKET_OPEN: $this -> processBracketSign($character); break;


            default: $this -> processDefault($character);
        }

        parent::append($character);
    }


    public function getOperator(): ?string {
        return $this -> operator;
    }


    public function getType(): ?string {
        return $this -> type;
    }


    public function getDirective(): ?string {
        return $this -> directive;
    }


    public function getValue(): ?string {
        return $this -> value;
    }

    public function isCaseSensitive(): bool {
        return false === $this -> caseInsensitive;
    }

    public function isConsuming(): bool {
        return $this -> isConsuming;
    }


    private function processBracketSign(string $character): void {

        if(null === $this -> type && null === $this -> operator && $character === CssInterpreter::BRACKET_OPEN) {

            $this -> isConsuming = true;
            $this -> bracketCount++;
            return;
        }

        if(null !== $this -> type && $character === CssInterpreter::BRACKET_CLOSED && 0 === $this -> enclosureCount % 2) {

            $this -> isConsuming = false;
            $this -> bracketCount++;
            return;
        }

        $this -> value ??= '';
        $this -> value .= $character;
    }


    private function processQuotesSign(string $character): void {

        if(null === $this -> enclosureType || $character === $this -> enclosureType) {

            $this -> enclosureType = $character;
            $this -> enclosureCount++;
            return;
        }

        $this -> value ??= '';
        $this -> value .= $character;
    }


    private function processOperatorSign(string $character): void {

        if(null === $this -> enclosureType) {

            $this -> operator ??= '';
            $this -> operator .= $character;
            return;
        }

        $this -> value ??= '';
        $this -> value .= $character;
    }


    private function processColonSign(): void {

        if(null === $this -> enclosureType && null === $this -> operator) {

            $this -> directive = $this -> type;
            $this -> type = null;
            return;
        }

        $this -> value ??= '';
        $this -> value .= CssInterpreter::COLON;
    }


    private function processDefault(string $character): void {

        if(null === $this -> type) {
            $this -> type = $character;
        }

        elseif(null !== $this -> type && null === $this -> operator) {
            $this -> type .= $character;
        }

        elseif(null !== $this -> operator) {

            $this -> value ??= '';
            $this -> value .= $character;
        }
    }
}