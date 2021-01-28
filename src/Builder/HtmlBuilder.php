<?php
declare(strict_types=1);

namespace sFire\Dom\Builder;

use sFire\Dom\Interpreter\Node\NodeTypes\ClosingNode as InterpreterClosingNode;
use sFire\Dom\Interpreter\Node\NodeTypes\OpeningNode;
use sFire\Dom\NodeManager\Interfaces\ParentInterface;
use sFire\Dom\NodeManager\Interfaces\TagInterface;
use sFire\Dom\NodeManager\Nodes\ClosingNode;
use sFire\Dom\NodeManager\Nodes\RootNode;


class HtmlBuilder extends BuilderAbstract {


    /**
     * @inheritdoc
     */
    protected const VOID_NODES = ['!DOCTYPE', 'area', 'base', 'br', 'col', 'embed', 'frame', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track'];


    /**
     * @inheritdoc
     */
    protected const RENDER_CHILDREN_AS_TEXT_NODES = ['style', 'textarea', 'script'];


    /**
     * @inheritdoc
     */
    protected const OPTIONAL_CLOSING_TAGS = [

        'li' => ['li'],
        'dt' => ['dt', 'dd'],
        'dd' => ['dd', 'dt'],
        'p'  => ['p'],
        'rt' => ['rt', 'rp'],
        'rp' => ['rt', 'rp'],
        'optgroup' => ['optgroup'],
        'colgroup' => ['colgroup'],
        'caption' => ['caption'],
        'thead' => ['tbody', 'tfoot'],
        'tbody' => ['tbody', 'tfoot'],
        'tfoot' => ['tfoot'],
        'tr' => ['tr'],
        'td' => ['td', 'th'],
        'th' => ['th', 'td'],
    ];


    /**
     * @inheritdoc
     */
    protected function createDomTree(): rootNode {

        $parent = null;
        $rootNode = new RootNode();
        $rootNode -> setIndexer($this -> indexer);
        $textMode = null;

        foreach($this -> interpreter -> getItems() as $index => $item) {

            //echo '<pre>' . print_r($item, true) . '</pre>';

            if(null === $textMode) {

                if((true === $item instanceof InterpreterClosingNode || true === $item instanceof OpeningNode) && true === in_array($item -> getTagName(), self::RENDER_CHILDREN_AS_TEXT_NODES, true)) {
                    $textMode = $item -> getTagName();
                }

                $node = $this -> createNode($item);
            }
            elseif(true === $item instanceof InterpreterClosingNode && $item -> getTagName() === $textMode) {

                $textMode = null;
                $node = $this -> createNode($item);
            }
            else {
                $node = $this -> createTextNode($item);
            }






            $node -> setBuilder($this);

            if(null !== $parent && $node instanceof ClosingNode) {

                $parent = $parent -> getParent();
                continue;
            }

            if($node instanceof ClosingNode) {
                continue;
            }

            if(null === $parent) {
                $rootNode -> appendChild($node);
            }
            elseif($parent instanceof ParentInterface) {

                //When current node may have an optional closing tag and is the same as the parent node, don't add it to the parent node.
                if($node instanceof TagInterface && true === in_array($parent -> getTagName(), self::OPTIONAL_CLOSING_TAGS[$node -> getTagName()] ?? [], true)) {
                    $parent = $parent -> getParent();
                }

                if(null !== $parent) {
                    $parent -> appendChild($node);
                }
                else {
                    $rootNode -> appendChild($node);
                }
            }

            if(true === $node -> canHaveChildren()) {
                $parent = $node;
            }
        }

        return $rootNode;
    }
}