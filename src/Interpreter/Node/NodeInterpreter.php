<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Node;

use sFire\Dom\Interpreter\Attribute\AttributeInterpreter;
use sFire\Dom\Interpreter\Character;
use sFire\Dom\Interpreter\Interfaces\AttributeInterface;
use sFire\Dom\Interpreter\Interfaces\ClosingNodeInterface;
use sFire\Dom\Interpreter\Interfaces\TagInterface;
use sFire\Dom\Interpreter\InterpreterAbstract;
use sFire\Dom\Interpreter\Node\NodeTypes\CdataNode;
use sFire\Dom\Interpreter\Node\NodeTypes\ClosingNode;
use sFire\Dom\Interpreter\Node\NodeTypes\CommentNode;
use sFire\Dom\Interpreter\Node\NodeTypes\DocTypeNode;
use sFire\Dom\Interpreter\Node\NodeTypes\NodeAbstract;
use sFire\Dom\Interpreter\Node\NodeTypes\OpeningNode;
use sFire\Dom\Interpreter\Node\NodeTypes\PrologNode;
use sFire\Dom\Interpreter\Node\NodeTypes\TextNode;


class NodeInterpreter extends InterpreterAbstract {


    private const START_COMMENT = '<!--';
    private const END_COMMENT = '-->';
    private const GREATER_THAN = '>';
    private const LOWER_THAN = '<';
    private const DOUBLE_QUOTE = '"';
    private const SINGLE_QUOTE = "'";
    private const FORWARD_SLASH = '/';
    private const QUESTION_MARK = '?';
    private const START_CDATA = '<![CDATA[';
    private const END_CDATA = ']]>';
    private const START_DOCTYPE = '<!DOCTYPE';


    /**
     * Contains instances of NodeAbstract
     * @var NodeAbstract[]
     */
    private array $nodes = [];


    /**
     * Contains an instance of NodeAbstract
     * @var null|NodeAbstract
     */
    private ?NodeAbstract $node = null;


    private AttributeInterpreter $attributeInterpreter;


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
        $this -> attributeInterpreter = new AttributeInterpreter($this -> character);

        for($i = 0; $i < $this -> character -> length(); $i++) {
            $this -> consume($i);
        }
    }


    /**
     * Returns all the interpreted nodes
     * @return NodeAbstract[]
     */
    public function getItems(): array {
        return $this -> nodes;
    }


    /**
     * Processes the current position of the data
     * @param int $position
     * @return void
     */
    private function consume(int $position): void {

        $character = $this -> character -> get($position);

        if(true === $this -> attributeInterpreter -> isConsuming()) {

            $this -> attributeInterpreter -> consume($position);

            if(null !== $this -> node) {
                $this -> node -> appendContent($character);
            }

            return;
        }

        switch($character) {

            //Lower than sign
            case static::LOWER_THAN: $this -> processLowerThanSign($position); break;

            //Closing tag
            case static::GREATER_THAN: $this -> processGreaterThanSign($position, $character); break;

            //Quotes
            case static::DOUBLE_QUOTE:
            case static::SINGLE_QUOTE: $this -> processQuotesSign($position, $character); break;

            //Whitespace
            case static::SPACE:
            case static::NEW_LINE:
            case static::TAB:
            case static::VERTICAL_TAB:
            case static::NUL_BYTE:
            case static::CARRIAGE: $this -> processWhiteSpace($character); break;

            //Process all other character
            default: $this -> processDefault($position, $character);
        }
    }


    /**
     * Processes the lower than sign
     * @param int $position
     * @return void
     */
    private function processLowerThanSign(int $position): void {

        //Retrieve the next character
        $nextCharacter = $this -> character -> next($position);

        if($this -> node instanceof CommentNode || $this -> node instanceof CdataNode) {

            $this -> node -> appendContent(static::LOWER_THAN);
            return;
        }

        //Only letters or numbers are valid tag names for the next character representing a opening node or looking for a forward slash for a closing node
        if(true === $this -> isAlphaNumeric($nextCharacter)) {

            $this -> node = new OpeningNode();
            $this -> node -> appendContent(static::LOWER_THAN);
            $this -> attributeInterpreter -> setNode(null);
            $this -> nodes[] = $this -> node;
            return;
        }

        if(static::FORWARD_SLASH === $nextCharacter) {

            $this -> node = new ClosingNode();
            $this -> node -> appendContent(static::LOWER_THAN);
            $this -> attributeInterpreter -> setNode(null);
            $this -> nodes[] = $this -> node;
            return;
        }

        //Check if next characters are the start of DOCTYPE
        if(static::START_DOCTYPE === strtoupper($this -> character -> range($position, $position + (strlen(static::START_DOCTYPE) - 1)))) {

            $this -> node = new DocTypeNode();
            $this -> node -> appendContent(static::LOWER_THAN);
            $this -> attributeInterpreter -> setNode(null);
            $this -> nodes[] = $this -> node;
            return;
        }

        //Check if next characters are the start of a comment
        if(static::START_COMMENT === strtoupper($this -> character -> range($position, $position + (strlen(static::START_COMMENT) - 1)))) {

            $this -> node = new CommentNode();
            $this -> node -> appendContent(static::LOWER_THAN);
            $this -> attributeInterpreter -> setNode(null);
            $this -> nodes[] = $this -> node;
            return;
        }

        //Check if next characters are the start of a CDATA node
        if(static::START_CDATA === strtoupper($this -> character -> range($position, $position + (strlen(static::START_CDATA) - 1)))) {

            $this -> node = new CdataNode();
            $this -> node -> appendContent(static::LOWER_THAN);
            $this -> attributeInterpreter -> setNode(null);
            $this -> nodes[] = $this -> node;
            return;
        }

        //Check if next character is a question mark for detecting a prolog tag
        if(static::QUESTION_MARK === $nextCharacter) {

            $this -> node = new PrologNode();
            $this -> node -> appendContent(static::LOWER_THAN);
            $this -> attributeInterpreter -> setNode(null);
            $this -> nodes[] = $this -> node;
            return;
        }

        //If there is no node current open, create one and add it to the stack
        if(null === $this -> node) {

            $this -> node = new TextNode();
            $this -> attributeInterpreter -> setNode(null);
            $this -> nodes[] = $this -> node;
        }

        $this -> node -> appendContent(static::LOWER_THAN);
    }


    /**
     * Processes the greater than sign
     * @param int $position
     * @param string $character
     * @return void
     */
    private function processGreaterThanSign(int $position, string $character): void {

        if(null === $this -> node) {
            $this -> createDefaultNode();
        }

        $this -> node -> appendContent($character);

        if($this -> node instanceof ClosingNodeInterface && static::FORWARD_SLASH === $this -> character -> previous($position, true)) {
            $this -> node -> setSelfClosingNode(true);
        }

        if($this -> node instanceof CdataNode && static::END_CDATA === strtoupper($this -> character -> range($position - (strlen(static::END_CDATA) - 1), $position))) {
            $this -> node = null;
        }
        elseif($this -> node instanceof CommentNode && static::END_COMMENT === strtoupper($this -> character -> range($position - (strlen(static::END_COMMENT) - 1), $position))) {
            $this -> node = null;
        }
        elseif($this -> node instanceof PrologNode && static::QUESTION_MARK === $this -> character -> previous($position, true)) {
            $this -> node = null;
        }
        elseif($this -> node instanceof TagInterface) {
            $this -> node = null;
        }
    }


    /**
     * Processes all the quote signs
     * @param int $position
     * @param string $character A quote character
     * @return void
     */
    private function processQuotesSign(int $position, string $character): void {

        if(null === $this -> node) {
            $this -> createDefaultNode();
        }

        if($this -> node instanceof AttributeInterface && true === $this -> isWhiteSpace($this -> character -> previous($position))) {

            $this -> attributeInterpreter -> enableConsuming();
            $this -> attributeInterpreter -> setNode($this -> node);
            $this -> attributeInterpreter -> consume($position);
            $this -> node -> appendContent($character);
            return;
        }

        if(false === $this -> attributeInterpreter -> isConsuming() && null !== $this -> attributeInterpreter -> getNode()) {

            $this -> node = new TextNode();
            $this -> nodes[] = $this -> node;
            $this -> attributeInterpreter -> setNode(null);
        }

        $this -> node -> appendContent($character);
    }


    /**
     * Processes all whitespace characters like \t \r \n and spaces
     * @param string $character A whitespace character
     * @return void
     */
    private function processWhiteSpace(string $character): void {

        if(null === $this -> node) {
            $this -> createDefaultNode();
        }

        if(false === $this -> attributeInterpreter -> isConsuming() && null !== $this -> attributeInterpreter -> getNode()) {

            $this -> node = new TextNode();
            $this -> nodes[] = $this -> node;
            $this -> attributeInterpreter -> setNode(null);
        }

        $this -> node -> appendContent($character);
    }


    /**
     * Process all other characters
     * @param int $position The current position/pointer of the data
     * @param string $character The current character
     * @return void
     */
    private function processDefault(int $position, string $character): void {

        if($this -> node instanceof CommentNode) {

            $this -> node -> appendContent($character);
            return;
        }

        if(null === $this -> node) {
            $this -> createDefaultNode();
        }

        if($this -> node instanceof AttributeInterface && null === $this -> attributeInterpreter -> getNode() && true === $this -> isWhiteSpace($this -> character -> previous($position))) {

            $this -> attributeInterpreter -> enableConsuming();
            $this -> attributeInterpreter -> setNode($this -> node);
            $this -> attributeInterpreter -> consume($position);
            $this -> node -> appendContent($character);
            return;
        }

        if(false === $this -> attributeInterpreter -> isConsuming() && null !== $this -> attributeInterpreter -> getNode()) {

            $this -> node = new TextNode();
            $this -> nodes[] = $this -> node;
            $this -> attributeInterpreter -> setNode(null);
        }

        if($this -> node instanceof TagInterface && true === $this -> isAlphaNumericDashedUnderscored($character)) {
            $this -> node -> appendTagName($character);
        }

        $this -> node -> appendContent($character);
    }


    /**
     * Creates a default node as a TextNode instance and adds it to the node list
     * @return void
     */
    private function createDefaultNode(): void {

        $this -> node = new TextNode();
        $this -> nodes[] = $this -> node;
    }
}