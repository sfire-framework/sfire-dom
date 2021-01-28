<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Css;

use sFire\Dom\Interpreter\Character;
use sFire\Dom\Interpreter\Css\Selectors\Asterisk;
use sFire\Dom\Interpreter\Css\Selectors\Attribute;
use sFire\Dom\Interpreter\Css\Selectors\Pseudo;
use sFire\Dom\Interpreter\Css\Selectors\Operator;
use sFire\Dom\Interpreter\Css\Selectors\Selector;
use sFire\Dom\Interpreter\Css\Selectors\SelectorAbstract;
use sFire\Dom\Interpreter\InterpreterAbstract;


class CssInterpreter extends InterpreterAbstract {


    public const DOUBLE_QUOTE = '"';
    public const SINGLE_QUOTE = "'";
    public const DOT = '.';
    public const ASTERISK = '*';
    public const POUND = '#';
    public const GREATER_THAN = '>';
    public const PLUS = '+';
    public const TILDE = '~';
    public const BRACKET_OPEN = '[';
    public const BRACKET_CLOSED = ']';
    public const PIPE = '|';
    public const POWER = '^';
    public const DOLLAR = '$';
    public const EQUAL = '=';
    public const COLON = ':';
    public const COMMA = ',';
    public const PARENTHESIS_OPEN = '(';
    public const PARENTHESIS_CLOSED = ')';


    /**
     * Contains an instance of SelectorAbstract
     * @var null|SelectorAbstract
     */
    private ?SelectorAbstract $item = null;


    /**
     * Contains all items
     * @var SelectorGroup[]
     */
    private array $items = [];



    /**
     * Contains an instance of RootGroup (element .foo, element .bar)
     * @var SelectorGroup|null
     */
    private ?SelectorGroup $selectorGroup = null;


    /**
     * Contains an instance of Character
     * @var Character
     */
    protected Character $character;


    /**
     * Constructor
     * @param string $data HTML or XML as a string
     */
    public function __construct(string $data) {

        $this -> character = new Character($data);

        for($i = 0; $i < $this -> character -> length(); $i++) {
            $this -> consume($i);
        }
    }


    /**
     * Returns all CSS selectors
     * @return SelectorGroup[]
     */
    public function getItems(): array {
        return $this -> items;
    }


    /**
     * Processes the current position of the data
     * @param int $position
     * @return void
     */
    private function consume(int $position): void {

        $character = $this -> character -> get($position);

        switch($character) {

            //Dot sign
            case self::DOT: $this -> processDotSign(); break;

            //Comma sign
            case self::COMMA: $this -> processCommaSign(); break;

            //Asterisk sign
            case self::ASTERISK: $this -> processAsteriskSign(); break;

            //Pound sign
            case self::POUND: $this -> processPoundSign(); break;

            //Colon sign
            case self::COLON: $this -> processColonSign(); break;

            //Parenthesis open sign
            case self::PARENTHESIS_OPEN: $this -> processParenthesisOpenSign(); break;

            //Parenthesis closed sign
            case self::PARENTHESIS_CLOSED: $this -> processParenthesisClosedSign(); break;

            //Process bracket open sign
            case self::BRACKET_OPEN: $this -> processBracketOpenSign(); break;

            //Process bracket close sign
            case self::BRACKET_CLOSED: $this -> processBracketCloseSign($position); break;

            //Process attribute operator signs
            case self::EQUAL:
            case self::PIPE:
            case self::POWER:
            case self::DOLLAR: $this -> processAttributeOperatorSign($character); break;

            //Process operator or attribute operator signs
            case self::PLUS:
            case self::TILDE:
            case self::GREATER_THAN: $this -> processOperatorSign($character); break;

            //Quotes
            case self::DOUBLE_QUOTE:
            case self::SINGLE_QUOTE: $this -> processQuotesSign($character); break;

            //Whitespace
            case self::NEW_LINE:
            case self::TAB:
            case self::VERTICAL_TAB:
            case self::NUL_BYTE:
            case self::CARRIAGE:
            case self::SPACE: $this -> processWhiteSpace($character); break;

            //Process all other character
            default: $this -> processDefault($character);
        }
    }


    /**
     * Processes the dot sign
     * @return void
     */
    private function processDotSign(): void {

        if(null === $this -> item) {

            $this -> item = new Selector();
            $this -> addItemToGroup($this -> item);
        }

        if($this -> item instanceof Selector) {

            $current = $this -> item -> getCurrent();

            if((true === $current instanceof Pseudo && true === $current -> isConsuming()) || (true === $current instanceof Attribute && true === $current -> isConsuming())) {

                $current -> append(self::DOT);
                return;
            }
        }

        $this -> item -> addClassName();
        $this -> item -> appendContent(self::DOT);
    }


    /**
     * Processes the comma sign
     * @return void
     */
    private function processCommaSign(): void {

        if(null === $this -> item) {

            $this -> item = new Selector();
            $this -> addItemToGroup($this -> item);
        }

        //Check if current state is not within a attribute
        if(false === $this -> item -> getCurrent() instanceof Attribute && false === $this -> item -> getCurrent() instanceof Pseudo) {

            $this -> item = null;
            $this -> selectorGroup = null;
            return;
        }

        $this -> processDefault(self::COMMA);
    }


    /**
     * Processes the pound sign
     * @return void
     */
    private function processPoundSign(): void {

        if(null === $this -> item) {

            $this -> item = new Selector();
            $this -> addItemToGroup($this -> item);
        }

        if($this -> item instanceof Selector) {

            $current = $this -> item -> getCurrent();

            if((true === $current instanceof Pseudo && true === $current -> isConsuming()) || (true === $current instanceof Attribute && true === $current -> isConsuming())) {

                $current -> append(self::POUND);
                return;
            }
        }

        $this -> item -> addId();
        $this -> item -> appendContent(self::POUND);
    }


    /**
     * Processes the colon sign
     * @return void
     */
    private function processColonSign(): void {

        if(null === $this -> item) {

            $this -> item = new Selector();
            $this -> addItemToGroup($this -> item);
        }

        $this -> item -> appendContent(self::COLON);

        if(false === $this -> item -> getCurrent() instanceof Attribute) {

            $this -> item -> addPseudo();
            return;
        }

        $this -> item -> append(self::COLON);
    }


    /**
     * Processes the open parenthesis sign
     * @return void
     */
    private function processParenthesisOpenSign(): void {

        if(null === $this -> item) {

            $this -> item = new Selector();
            $this -> addItemToGroup($this -> item);
        }

        if(true === $this -> item -> getCurrent() instanceof Pseudo) {
            $this -> item -> append(self::PARENTHESIS_OPEN);
        }

        $this -> item -> appendContent(self::PARENTHESIS_OPEN);
    }


    /**
     * Processes the closed parenthesis sign
     * @return void
     */
    private function processParenthesisClosedSign(): void {

        if(null === $this -> item) {

            $this -> item = new Selector();
            $this -> addItemToGroup($this -> item);
        }

        if(true === $this -> item -> getCurrent() instanceof Pseudo) {
            $this -> item -> append(self::PARENTHESIS_CLOSED);
        }

        $this -> item -> appendContent(self::PARENTHESIS_CLOSED);
    }


    /**
     * Processes the asterisk sign
     * @return void
     */
    private function processAsteriskSign(): void {

        if(null !== $this -> item) {

            $item = $this -> item;

            if($item instanceof Selector) {

                if(true === $item -> getCurrent() instanceof Attribute || true === $item -> getCurrent() instanceof Pseudo) {

                    $this -> item -> append(self::ASTERISK);
                    return;
                }
            }
        }

        $this -> item = new Asterisk();
        $this -> item -> append(self::ASTERISK);
        $this -> addItemToGroup($this -> item);
        $this -> item = null;
    }


    /**
     * Processes a attribute operator sign
     * @param string $character A single character
     * @return void
     */
    private function processAttributeOperatorSign(string $character): void {

        if(null === $this -> item) {

            $this -> item = new Selector();
            $this -> addItemToGroup($this -> item);
        }

        $this -> item -> appendContent($character);

        if(true === $this -> item -> getCurrent() instanceof Attribute) {
            $this -> item -> append($character);
        }
    }


    /**
     * Processes the operator sign
     * @param string $character A single character
     * @return void
     */
    private function processOperatorSign(string $character): void {

        if($this -> item instanceof Selector) {

            $current = $this -> item -> getCurrent();

            if(true === $current instanceof Attribute && false === $current instanceof Pseudo && true === $current -> isConsuming()) {

                $this -> processAttributeOperatorSign($character);
                return;
            }
        }


        $this -> item = new Operator();
        $this -> addItemToGroup($this -> item);
        $this -> item -> append($character);
        $this -> item = null;
    }


    /**
     * Processes the quote sign
     * @param string $character A single character
     * @return void
     */
    private function processQuotesSign(string $character): void {

        if(null === $this -> item) {

            $this -> item = new Selector();
            $this -> addItemToGroup($this -> item);
        }

        $current = $this -> item -> getCurrent();

        if((true === $current instanceof Pseudo && true === $current -> isConsuming()) || (true === $current instanceof Attribute && true === $current -> isConsuming())) {

            $current -> append($character);
            $this -> item -> appendContent($character);
            return;
        }

        $this -> item -> appendContent($character);

        if(true === $this -> item -> getCurrent() instanceof Attribute) {
            $this -> item -> append($character);
        }
    }


    /**
     * Processes the open bracket sign
     * @return void
     */
    private function processBracketOpenSign(): void {

        if(null === $this -> item) {

            $this -> item = new Selector();
            $this -> addItemToGroup($this -> item);
        }

        $current = $this -> item -> getCurrent();

        if(false === $current instanceof Attribute && false === $current instanceof Pseudo) {

            $this -> item -> addAttribute();
            $this -> item -> append(self::BRACKET_OPEN);
            $this -> item -> appendContent(self::BRACKET_OPEN);
            return;
        }

        if(true === $current instanceof Attribute && false === $current -> isConsuming()) {

            $this -> item -> addAttribute();
            $this -> item -> append(self::BRACKET_OPEN);
            $this -> item -> appendContent(self::BRACKET_OPEN);
            return;
        }

        $this -> processDefault(self::BRACKET_OPEN);
    }


    /**
     * Processes the closed bracket sign
     * @param int $position
     * @return void
     */
    private function processBracketCloseSign(int $position): void {

        $item = $this -> item;

        if($item instanceof Selector && true === $item -> getCurrent() instanceof Attribute) {

            $current = $item -> getCurrent();

            if(true === $current instanceof Attribute) {

                $item -> append(self::BRACKET_CLOSED);
                $item -> appendContent(self::BRACKET_CLOSED);

                if(false === $current -> isConsuming() && true === $this -> isWhiteSpace($this -> character -> next($position))) {
                    $this -> item = null;
                }

                return;
            }
        }

        $this -> processDefault(self::BRACKET_CLOSED);
    }


    /**
     * Processes whitespace characters
     * @param string $character A single whitespace character
     * @return void
     */
    private function processWhiteSpace(string $character): void {

        if($this -> item instanceof Selector && true === $this -> item -> getCurrent() instanceof Attribute) {

            $this -> item -> appendContent($character);
            $this -> item -> append($character);
            return;
        }

        $this -> item = null;
    }


    /**
     * Processes all other characters
     * @param string $character Represents a single character that didn't processed through other methods
     * @return void
     */
    private function processDefault(string $character): void {

        if(null === $this -> item) {

            $this -> item = new Selector();
            $this -> item -> addTag();
            $this -> addItemToGroup($this -> item);
        }

        if($this -> item instanceof Selector) {

            $current = $this -> item -> getCurrent();

            if((true === $current instanceof Pseudo && true === $current -> isConsuming()) || (true === $current instanceof Attribute && true === $current -> isConsuming())) {

                $current -> append($character);
                $this -> item -> appendContent($character);
                return;
            }
        }

        $this -> item -> appendContent($character);
        $this -> item -> append($character);
    }


    /**
     * Creates a new rootGroup and selectorGroup instance if not already exists and adds the given item to the selector group
     * @param SelectorAbstract $item
     * @return void
     */
    private function addItemToGroup(SelectorAbstract $item): void {

        if(null === $this -> selectorGroup) {

            $this -> selectorGroup = new SelectorGroup();
            $this -> items[] = $this -> selectorGroup;
        }

        $this -> selectorGroup -> addItem($item);
    }
}