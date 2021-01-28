<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Attribute;

use sFire\Dom\Interpreter\Character;
use sFire\Dom\Interpreter\Interfaces\AttributeInterface;
use sFire\Dom\Interpreter\Interfaces\ClosingNodeInterface;
use sFire\Dom\Interpreter\InterpreterAbstract;


class AttributeInterpreter extends InterpreterAbstract {


    private const DOUBLE_QUOTE = '"';
    private const SINGLE_QUOTE = "'";
    private const EQUALS = '=';
    private const COLON = ':';
    protected const NEW_LINE = "\n";
    protected const TAB = "\t";
    protected const CARRIAGE = "\r";
    protected const SPACE = ' ';
    protected const NUL_BYTE = "\0";
    protected const QUESTION_MARK = '?';
    protected const VERTICAL_TAB = "\x0B";
    protected const GREATER_THAN = '>';
    protected const FORWARD_SLASH = '/';


    private ?Attribute $attribute = null;


    private array $attributes = [];
    private bool $isConsuming = false;

    private ?AttributeInterface $node = null;

    private Character $character;


    /**
     * Constructor
     * @param Character $character
     */
    public function __construct(Character $character) {
        $this -> character = $character;
    }

    public function isConsuming(): bool {
        return $this -> isConsuming;
    }

    public function getNode(): ?AttributeInterface {
        return $this -> node;
    }

    public function enableConsuming(): void {
        $this -> isConsuming = true;
    }

    public function disableConsuming(): void {
        $this -> isConsuming = false;
    }


    public function setNode(?AttributeInterface $node): void {
        $this -> node = $node;
    }


    /**
     * Returns all the interpreted attributes
     * @return Attribute[]
     */
    public function getItems(): array {
        return $this -> attributes;
    }


    /**
     * Processes the current position of the data
     * @param int $position
     * @return void
     */
    public function consume(int $position): void {

        $character = $this -> character -> get($position);

        switch($character) {

            //Equal sign
            case self::EQUALS: $this -> processEqualSign(); break;

            //Colon sign
            case self::COLON: $this -> processColonSign(); break;

            //Greater than sign
            case self::GREATER_THAN: $this -> processGreaterThanSign($position); break;

            //Forward slash sign
            case self::FORWARD_SLASH: $this -> processForwardSlashSign($position); break;

            //Forward slash sign
            case self::QUESTION_MARK: $this -> processQuestionMarkSign($position); break;

            //Quotes
            case self::DOUBLE_QUOTE:
            case self::SINGLE_QUOTE: $this -> processQuotesSign($position, $character); break;

            //Whitespace
            case self::SPACE:
            case self::NEW_LINE:
            case self::TAB:
            case self::VERTICAL_TAB:
            case self::NUL_BYTE:
            case self::CARRIAGE: $this -> processWhiteSpace($character); break;

            //Process all other character
            default: $this -> processDefault($character);
        }
    }


    private function processEqualSign(): void {

        if(null === $this -> attribute) {

            $this -> attribute = new Attribute();
            $this -> node -> addAttribute($this -> attribute);
        }

        if(null === $this -> attribute -> getEnclosureType()) {
            $this -> attribute -> setState(Attribute::STATE_VALUE);
        }
        else {
            $this -> attribute -> append(static::EQUALS);
        }
    }


    private function processQuestionMarkSign(int $position): void {

        if(null === $this -> attribute || null === $this -> attribute -> getEnclosureType()) {

            if(static::GREATER_THAN === $this -> character -> next($position, true)) {
                return;
            }
        }

        if(null === $this -> attribute) {

            $this -> attribute = new Attribute();
            $this -> node -> addAttribute($this -> attribute);
        }

        $this -> attribute -> append(static::QUESTION_MARK);
    }


    private function processGreaterThanSign(int $position): void {

        if(null === $this -> attribute || null === $this -> attribute -> getEnclosureType()) {

            if($this -> node instanceof ClosingNodeInterface && static::FORWARD_SLASH === $this -> character -> previous($position, true)) {
                $this -> node -> setSelfClosingNode(true);
            }

            $this -> attribute = null;
            $this -> disableConsuming();
            return;
        }

        $this -> attribute -> append(static::GREATER_THAN);
    }


    private function processForwardSlashSign(int $position): void {

        if(null === $this -> attribute || null === $this -> attribute -> getEnclosureType()) {

            if(static::GREATER_THAN === $this -> character -> next($position, true)) {
                return;
            }
        }

        if(null === $this -> attribute) {

            $this -> attribute = new Attribute();
            $this -> node -> addAttribute($this -> attribute);
        }

        $this -> attribute -> append(static::FORWARD_SLASH);
    }


    private function processColonSign(): void {

        if(null !== $this -> attribute && null !== $this -> attribute -> getType() && null === $this -> attribute -> getEnclosureType()) {

            $this -> attribute -> setDirective($this -> attribute -> getType());
            $this -> attribute -> setType(null);
            return;
        }

        if(null !== $this -> attribute) {
            $this -> attribute -> append(static::COLON);
        }
        else {

            $this -> attribute = new Attribute();
            $this -> node -> addAttribute($this -> attribute);
            $this -> attribute -> setDirective(static::COLON);
        }
    }


    /**
     * Processes all the quote signs
     * @param int $position
     * @param string $character
     * @return void
     */
    private function processQuotesSign(int $position, string $character): void {

        if(null === $this -> attribute) {

            $this -> attribute = new Attribute();
            $this -> node -> addAttribute($this -> attribute);
        }

        if($character === $this -> attribute -> getEnclosureType()) {

            $this -> attribute = null;
            return;
        }

        if(null === $this -> attribute -> getEnclosureType()) {

            if(null === $this -> attribute -> getType()) {

                $this -> attribute -> setEnclosureType($character);
                $this -> attribute -> setState(Attribute::STATE_VALUE);
            }
            elseif(null !== $this -> attribute -> getType() && static::EQUALS === $this -> character -> previous($position, true)) {

                $this -> attribute -> setEnclosureType($character);
                $this -> attribute -> setState(Attribute::STATE_VALUE);
            }
        }
        elseif($character !== $this -> attribute -> getEnclosureType()) {
            $this -> attribute -> append($character);
        }
    }


    /**
     * Processes all whitespace characters like \t \r \n and spaces
     * @param string $character A whitespace character
     * @return void
     */
    private function processWhiteSpace(string $character): void {

        if(null !== $this -> attribute) {

            if(null !== $this -> attribute -> getType() && null === $this -> attribute -> getEnclosureType()) {
                $this -> attribute = null;
            }
            elseif(null !== $this -> attribute -> getValue()) {
                $this -> attribute -> appendValue($character);
            }
        }
    }


    /**
     * Process all other characters
     * @param string $character The current character
     * @return void
     */
    private function processDefault(string $character): void {

        if(null === $this -> attribute) {

            $this -> attribute = new Attribute();
            $this -> node -> addAttribute($this -> attribute);
        }

        if(false === $this -> isAlphaNumeric($character) && null === $this -> attribute -> getType() && null === $this -> attribute -> getEnclosureType()) {

            $this -> attribute -> setDirective($character);
            return;
        }

        $this -> attribute -> append($character);
    }
}