<?php
declare(strict_types=1);

namespace sFire\Dom;

use BadMethodCallException;
use sFire\Dom\Builder\BuilderAbstract;
use sFire\Dom\Builder\HtmlBuilder;
use sFire\Dom\Interpreter\Css\CssInterpreter;
use sFire\Dom\Interpreter\Node\NodeInterpreter;
use sFire\Dom\NodeManager\Nodes\CdataNode;
use sFire\Dom\NodeManager\Nodes\CommentNode;
use sFire\Dom\NodeManager\Nodes\DocTypeNode;
use sFire\Dom\NodeManager\Nodes\OpeningNode;
use sFire\Dom\NodeManager\Nodes\PrologNode;
use sFire\Dom\NodeManager\Nodes\RootNode;
use sFire\Dom\NodeManager\Nodes\SelfClosingNode;
use sFire\Dom\NodeManager\Nodes\TextNode;


class DomParser {


    public const HTML = 'html';
    private const XML = 'xml';


    /**
     * Contains the original dom code to be parsed
     * @var string
     */
    private string $domCode;


    /**
     * Contains the type of dome code
     * @var string
     */
    private string $type;


    /**
     * Contains an instance of a BuilderAbstract
     * @var BuilderAbstract
     */
    //private BuilderAbstract $builder;
    public BuilderAbstract $builder;


    /**
     * Constructor
     * @param string $domCode
     * @param string $type
     */
    public function __construct(string $domCode, string $type = self::HTML) {

        $this -> domCode = $domCode;
        $this -> type = $type;
        $this -> createDom();
    }


    /**
     * Finds nodes based on a CSS selector string, i.e. ".foo" or "div li.bar"
     * @param string $selector
     * @return DomNodes
     */
    public function find(string $selector): DomNodes {

        $cssSelector = new CssInterpreter($selector);
        $nodes = $this -> builder -> filter($cssSelector, $this -> builder -> getRootNode());

        return new DomNodes($nodes, $this);
    }


    /**
     * Returns all the root nodes within a DomNodes instance
     * @return DomNodes
     */
    public function root(): DomNodes {
        return new DomNodes($this -> builder -> getDomTree(), $this);
    }


    /**
     * Returns an instance of DomNodes with all found text nodes
     * @return DomNodes
     */
    public function filterTextNodes(): DomNodes {
        return new DomNodes($this -> getNodeByType([TextNode::class]), $this);
    }


    /**
     * Returns an instance of DomNodes with all found comment nodes
     * @return DomNodes
     */
    public function filterCommentNodes(): DomNodes {
        return new DomNodes($this -> getNodeByType([CommentNode::class]), $this);
    }


    /**
     * Returns an instance of DomNodes with all found prolog nodes
     * @return DomNodes
     */
    public function filterPrologNodes(): DomNodes {
        return new DomNodes($this -> getNodeByType([PrologNode::class]), $this);
    }


    /**
     * Returns an instance of DomNodes with all found CData nodes
     * @return DomNodes
     */
    public function filterCdataNodes(): DomNodes {
        return new DomNodes($this -> getNodeByType([CdataNode::class]), $this);
    }


    /**
     * Returns an instance of DomNodes with all found other nodes
     * @return DomNodes
     */
    public function filterParentNodes(): DomNodes {
        return new DomNodes($this -> getNodeByType([RootNode::class, OpeningNode::class, SelfClosingNode::class]), $this);
    }


    /**
     * Returns an instance of DomNodes with all found Doctype nodes
     * @return DomNodes
     */
    public function filterDocTypeNodes(): DomNodes {
        return new DomNodes($this -> getNodeByType([DocTypeNode::class]), $this);
    }


    /**
     * Returns the type
     * @return string
     */
    public function getType(): string {
        return $this -> type;
    }


    /**
     * Converts all node to a XML or HTML string
     * @return string
     */
    public function render(): string {

        $nodeStack = [];

        foreach($this -> builder -> getDomTree() as $node) {
            $nodeStack[] = $node -> render();
        }

        return implode('', $nodeStack);
    }


    /**
     * Populates a builder with DOM node objects from converting the dom code
     * @return void
     */
    private function createDom(): void {

        $interpreter = new NodeInterpreter($this -> domCode);

        switch($this -> type) {

            case self::HTML: $builder = new HtmlBuilder($interpreter); break;
            case self::XML: throw new BadMethodCallException('XML is not yet supported');
            default: throw new BadMethodCallException(sprintf('Undefined type "%s". Only %s is allowed.', $this -> type, self::HTML));
        }

        $this -> builder = $builder;
    }


    /**
     * Traverses through all child nodes recursively of a given node and checks if the node type exists in a provided node type array and returns these nodes in a new instance of DomNodes
     * @param string[] $nodeTypes The target node to traverse through
     * @return array
     */
    private function getNodeByType(array $nodeTypes): array {

        $nodeList = [];

        $this -> builder -> getRootNode() -> getChildrenRecursive(function($node) use ($nodeTypes, &$nodeList) {

            if(true === in_array(get_class($node), $nodeTypes, true)) {
                $nodeList[$node -> getObjectId()] = $node;
            }
        });

        return $nodeList;
    }
}