<?php
declare(strict_types=1);

namespace sFire\Dom\Builder;

use RuntimeException;
use sFire\Dom\Interpreter\Css\CssInterpreter;
use sFire\Dom\Interpreter\Css\Selectors\Operator;
use sFire\Dom\Interpreter\Css\Selectors\Selector;
use sFire\Dom\Interpreter\Interfaces\InterpreterInterface;
use sFire\Dom\Interpreter\Interfaces\TagInterface;
use sFire\Dom\Interpreter\Node\NodeTypes\NodeAbstract as InterpreterNodeAbstract;
use sFire\Dom\Interpreter\Node\NodeTypes\ClosingNode as InterpreterClosingNode;
use sFire\Dom\Interpreter\Node\NodeTypes\OpeningNode as InterpreterOpeningNode;
use sFire\Dom\Interpreter\Node\NodeTypes\TextNode as InterpreterTextNode;
use sFire\Dom\Interpreter\Node\NodeTypes\PrologNode as InterpreterPrologNode;
use sFire\Dom\Interpreter\Node\NodeTypes\CdataNode as InterpreterCdataNode;
use sFire\Dom\Interpreter\Node\NodeTypes\CommentNode as InterpreterCommentNode;
use sFire\Dom\Interpreter\Node\NodeTypes\DocTypeNode as InterpreterDocTypeNode;
use sFire\Dom\NodeManager\Interfaces\IndexInterface;
use sFire\Dom\NodeManager\Nodes\CdataNode;
use sFire\Dom\NodeManager\Nodes\ClosingNode;
use sFire\Dom\NodeManager\Nodes\CommentNode;
use sFire\Dom\NodeManager\Nodes\DocTypeNode;
use sFire\Dom\NodeManager\Nodes\NodeAbstract;
use sFire\Dom\NodeManager\Nodes\OpeningNode;
use sFire\Dom\NodeManager\Nodes\PrologNode;
use sFire\Dom\NodeManager\Nodes\RootNode;
use sFire\Dom\NodeManager\Nodes\SelfClosingNode;
use sFire\Dom\NodeManager\Nodes\TextNode;


abstract class BuilderAbstract implements BuilderInterface {


    /**
     * Contains a tree of dom nodes as an array
     * @var RootNode
     */
    protected RootNode $rootNode;


    /**
     * Contains an instance of Indexer
     * @var Indexer
     */
    //protected Indexer $indexer;
    public Indexer $indexer;


    /**
     * Contains an instance of InterpreterInterface
     * @var InterpreterInterface
     */
    protected InterpreterInterface $interpreter;

    /**
     * Contains all the nodes with no end tag
     * @var array
     */
    protected const VOID_NODES = [];


    /**
     * Contains all nodes that should render their children as normal text
     * @var array
     */
    protected const RENDER_CHILDREN_AS_TEXT_NODES = [];


    /**
     * Contains all node types that have optional closing tags
     * @var array
     */
    protected const OPTIONAL_CLOSING_TAGS = [];


    /**
     * Constructor
     * @param InterpreterInterface $interpreter
     */
    public function __construct(InterpreterInterface $interpreter) {

        $this -> indexer = new Indexer();
        $this -> interpreter = $interpreter;
        $this -> rootNode = $this -> createDomTree();
    }


    public function getRootNode(): RootNode {
        return $this -> rootNode;
    }


    /**
     * Reduces the set of matched nodes to those that match the selector
     * @param CssInterpreter $selectorRoot
     * @param NodeAbstract $parentNode
     * @return NodeAbstract[]
     */
    public function filter(CssInterpreter $selectorRoot, NodeAbstract $parentNode): array {

        $foundNodes = [];

        foreach($selectorRoot -> getItems() as $selectorGroup) {

            $selectors = $selectorGroup -> getItems(true);
            $endNodeSelector = array_shift($selectors);

            if(false === $endNodeSelector instanceof Selector) {
                break;
            }

            $endNodeParents = [];
            $previousNodes = [];
            $endNodes = $this -> indexer -> getNodesBySelector($endNodeSelector);

            foreach($endNodes as $endNodeObjectId => $endNode) {

                $endNodeParents[$endNodeObjectId] = $endNode -> getParentNodeIds();
                $previousNodes[$endNodeObjectId] = $endNode;
            }

            $operator = null;

            foreach($selectors as $selector) {

                //Set the operator if the selector contains an Operator instance
                if($selector instanceof Operator) {

                    $operator = $selector;
                    continue;
                }

                //If the current selector is not an instance of Selector, something is wrong
                if(false === $selector instanceof Selector) {
                    break;
                }

                $parentNodes = $this -> indexer -> getNodesBySelector($selector);
                $nodes = [];

                foreach($endNodes as $endNodeObjectId => $endNode) {

                    if(0 === count($parentNodes) || false === in_array($parentNode -> getObjectId(), $endNodeParents[$endNodeObjectId], true)) {

                        unset($endNodes[$endNodeObjectId]);
                        break;
                    }

                    if(null !== $operator) {

                        switch($operator -> getContent()) {

                            //Match greater than operator ">"
                            case CssInterpreter::GREATER_THAN:

                                $parentObjectId = array_shift($endNodeParents[$endNodeObjectId]);

                                foreach($parentNodes as $parent) {

                                    if($parentObjectId === $parent -> getObjectId()) {

                                        $previousNodes[$endNodeObjectId] = $parent;
                                        $nodes[$endNodeObjectId] = $endNode;
                                        break;
                                    }
                                }

                                break;

                            //Match plus operator "+"
                            case CssInterpreter::PLUS:

                                foreach($parentNodes as $parent) {

                                    $next = $parent -> next();

                                    if($next === $previousNodes[$endNodeObjectId]) {

                                        $previousNodes[$endNodeObjectId] = $parent;
                                        $nodes[$endNodeObjectId] = $endNode;
                                        break;
                                    }
                                }

                                break;

                            //Match tilde operator "~"
                            case CssInterpreter::TILDE:

                                foreach($parentNodes as $parent) {

                                    $nextSibling = $parent -> next();

                                    while($nextSibling) {

                                        if($nextSibling === $previousNodes[$endNodeObjectId]) {

                                            $previousNodes[$endNodeObjectId] = $parent;
                                            $nodes[$endNodeObjectId] = $endNode;
                                            break;
                                        }

                                        $nextSibling = $nextSibling -> next();
                                    }
                                }

                                break;
                        }

                        continue;
                    }

                    foreach($endNodeParents[$endNodeObjectId] as $index => $parentObjectId) {

                        if(true === isset($parentNodes[$parentObjectId])) {

                            $nodes[$endNodeObjectId] = $endNode;
                            $previousNodes[$endNodeObjectId] = $parentNodes[$parentObjectId];
                            break;
                        }

                        unset($endNodeParents[$endNodeObjectId][$index]);
                    }
                }

                $operator = null;
                $endNodes = $nodes;
            }

            $foundNodes[] = $endNodes;
        }

        return array_replace([], ...$foundNodes);
    }


    /**
     * @inheritdoc
     */
    public function getDomTree(): array {
        return $this -> rootNode -> getChildren();
    }


    /**
     * Creates and returns a RoodNode instance which includes dom nodes as a tree with children and parent nodes
     * @return RootNode
     */
    abstract protected function createDomTree(): RootNode;


    /**
     * Creates an instance of NodeAbstract based on a given Element instance
     * @param InterpreterNodeAbstract $sourceNode
     * @return NodeAbstract
     */
    protected function createNode(InterpreterNodeAbstract $sourceNode): NodeAbstract {

        if($sourceNode instanceof InterpreterOpeningNode || $sourceNode instanceof InterpreterClosingNode) {
            return $this -> buildNode($sourceNode);
        }

        if($sourceNode instanceof InterpreterTextNode) {
            return $this -> createTextNode($sourceNode);
        }

        if($sourceNode instanceof InterpreterPrologNode) {

            $node = new PrologNode();
            $node -> setContent($sourceNode -> getContent());
            return $node;
        }

        if($sourceNode instanceof InterpreterCdataNode) {

            $node = new CdataNode();
            $node -> setContent($sourceNode -> getContent());
            return $node;
        }

        if($sourceNode instanceof InterpreterCommentNode) {

            $node = new CommentNode();
            $node -> setContent($sourceNode -> getContent());
            return $node;
        }

        if($sourceNode instanceof InterpreterDocTypeNode) {

            $node = new DocTypeNode();
            $node -> setContent($sourceNode -> getContent());
            return $node;
        }

        throw new RuntimeException(sprintf('Undefined node type "%s"', get_class($sourceNode)));
    }


    protected function createTextNode(InterpreterNodeAbstract $sourceNode): NodeAbstract {

        $node = new TextNode();
        $node -> setContent($sourceNode -> getContent());
        return $node;
    }


    /**
     * Returns a new instance of ClosingNode, SelfClosingNode or OpeningNode based on a Element instance
     * @param TagInterface $sourceNode
     * @return ClosingNode|SelfClosingNode|OpeningNode
     */
    protected function buildNode(TagInterface $sourceNode): NodeAbstract {

        if($sourceNode instanceof InterpreterClosingNode) {

            $node = new ClosingNode();
            $node -> setTagName($sourceNode -> getTagName());

            if($node instanceof IndexInterface) {
                $node -> setIndexer($this -> indexer);
            }

            return $node;
        }

        if($sourceNode instanceof InterpreterOpeningNode && true === $sourceNode -> isSelfClosing()) {

            $node = new SelfClosingNode();

            if($node instanceof IndexInterface) {
                $node -> setIndexer($this -> indexer);
            }

            $node -> setTagName($sourceNode -> getTagName());
            $node -> setAttributes($sourceNode -> getAttributes());

            return $node;
        }

        $node = new OpeningNode();
        $node -> setTagName($sourceNode -> getTagName());

        if($node instanceof IndexInterface) {
            $node -> setIndexer($this -> indexer);
        }

        if($sourceNode instanceof InterpreterOpeningNode) {
            $node -> setAttributes($sourceNode -> getAttributes());
        }

        //Check if node can have children
        if(true === in_array($node -> getTagName(), static::VOID_NODES, true)) {
            $node -> setCanHaveChildren(false);
        }

        if(true === in_array($node -> getTagName(), static::RENDER_CHILDREN_AS_TEXT_NODES, true)) {
            $node -> setRenderChildrenAsText(true);
        }

        return $node;
    }
}