<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Nodes;

use sFire\Dom\Interpreter\Css\CssInterpreter;
use sFire\Dom\NodeManager\Interfaces\IndexInterface;
use sFire\Dom\NodeManager\Interfaces\ParentInterface;
use sFire\Dom\NodeManager\Interfaces\RenderChildrenBehaviorInterface;
use sFire\Dom\NodeManager\Traits\AttributeTrait;
use sFire\Dom\NodeManager\Traits\IndexTrait;
use sFire\Dom\NodeManager\Traits\Children;
use sFire\Dom\NodeManager\Traits\TagTrait;


abstract class ParentNodeAbstract extends NodeAbstract implements RenderChildrenBehaviorInterface, ParentInterface, IndexInterface {


    use Children;
    use IndexTrait;
    use TagTrait;
    use AttributeTrait;


    /**
     * Contains an array with instances of NodeAbstract representing child nodes with their object id as key
     * @var NodeAbstract[]
     */
    protected array $children = [];


    /**
     * Clones all the children and resets the children array with new object ids
     */
    public function __clone() {

        parent::__clone();

        $children = [];

        foreach($this -> children as $id => $child) {

            $node = clone($child);
            $children[$node -> getObjectId()] = $node;
            unset($this -> children[$id]);
        }

        $this -> children = $children;
    }


    /**
     * Appends an instance of NodeAbstract as child to the end of the children list
     * @param NodeAbstract $child
     * @return void
     */
    public function appendChild(NodeAbstract $child): void {

        $parent = $child -> getParent();

        if($parent instanceof ParentInterface) {
            $parent -> removeChild($child);
        }

        $child -> setParent($this);
        $this -> children[$child -> getObjectId()] = $child;

        if($child instanceof IndexInterface) {
            $this -> getIndexer() -> addNode($child);
        }
    }


    /**
     * Prepends an instance of NodeAbstract as child at the begin of the children list
     * @param NodeAbstract $child
     * @return void
     */
    public function prependChild(NodeAbstract $child): void {

        $parent = $child -> getParent();

        if($parent instanceof ParentInterface) {
            $parent -> removeChild($child);
        }

        $child -> setParent($this);
        $this -> children = array_replace([$child -> getObjectId() => $child], $this -> children);

        if($child instanceof IndexInterface) {
            $this -> getIndexer() -> addNode($child);
        }
    }


    /**
     * Inserts an instance of NodeAbstract before a given NodeAbstract target child
     * @param NodeAbstract $targetChild
     * @param NodeAbstract $afterChild
     * @return void
     */
    public function insertChildAfter(NodeAbstract $targetChild, NodeAbstract $afterChild): void {

        $index = array_search($afterChild -> getObjectId(), array_keys($this -> children), true);

        if(false === $index) {
            return;
        }

        $beforeSlice = array_slice($this -> children, 0, ((int) $index) + 1, true);
        $afterSlice = array_slice($this -> children, $index + 1, null, true);
        $this -> children = array_replace($beforeSlice, [$targetChild -> getObjectId() => $targetChild], $afterSlice);

        $targetChild -> setParent($this);

        if($targetChild instanceof IndexInterface) {
            $this -> getIndexer() -> addNode($targetChild);
        }
    }


    /**
     * Inserts an instance of NodeAbstract after a given NodeAbstract target child
     * @param NodeAbstract $targetChild
     * @param NodeAbstract $beforeChild
     * @return void
     */
    public function insertChildBefore(NodeAbstract $targetChild, NodeAbstract $beforeChild): void {

        $index = array_search($beforeChild -> getObjectId(), array_keys($this -> children), true);

        if(false === $index) {
            return;
        }

        $beforeSlice = array_slice($this -> children, 0, (int) $index, true);
        $afterSlice = array_slice($this -> children, $index, null, true);
        $this -> children = array_replace($beforeSlice, [$targetChild -> getObjectId() => $targetChild], $afterSlice);

        $targetChild -> setParent($this);

        if($targetChild instanceof IndexInterface) {
            $this -> getIndexer() -> addNode($targetChild);
        }
    }


    /**
     * Removes the current node with all its child nodes
     * @return void
     */
    public function remove(): void {

        $this -> getIndexer() -> removeByNode($this);

        foreach($this -> getChildren() as $child) {
            $child -> remove();
        }

        $parent = $this -> getParent();

        if($parent instanceof ParentInterface) {
            $parent -> removeChild($this);
        }
    }


    /**
     * Removes a given child node from the children array list
     * @param NodeAbstract $targetChild
     * @return void
     */
    public function removeChild(NodeAbstract $targetChild): void {

        unset($this -> children[$targetChild -> getObjectId()]);

        if($targetChild instanceof IndexInterface) {
            $this -> indexer -> removeByNode($targetChild);
        }
    }


    public function getFirstChild(array $excludeNodeTypes = []): ?NodeAbstract {

        foreach($this -> children as $child) {

            if(false === in_array(get_class($child), $excludeNodeTypes, true)) {
                return $child;
            }
        }

        return null;
    }

    public function getLastChild(array $excludeNodeTypes = []): ?NodeAbstract {

        foreach(array_reverse($this -> children) as $child) {

            if(false === in_array(get_class($child), $excludeNodeTypes, true)) {
                return $child;
            }
        }

        return null;
    }


    /**
     * Returns an array with instances of NodeAbstract representing child nodes with their object id as key
     * @return NodeAbstract[]
     */
    public function getChildren(): array {
        return $this -> children;
    }


    public function findChildren(string $selector): array {

        $cssSelector = new CssInterpreter($selector);
        return $this -> getBuilder() -> filter($cssSelector, $this);
    }


    /**
     * Traverses through all child nodes recursively of a given target node and executes a provided callback function foreach found child node
     * @param callable $callback A callable method which will be executed for every found child node
     * @param NodeAbstract|null $targetNode The target node to traverse through, defaults the current node
     * @return void
     */
    public function getChildrenRecursive(callable $callback, NodeAbstract $targetNode = null): void {

        $targetNode = $targetNode ?? $this;

        if($targetNode instanceof ParentInterface) {

            foreach($targetNode -> getChildren() as $child) {

                $callback($child);

                if($child instanceof ParentInterface) {
                    $this -> getChildrenRecursive($callback, $child);
                }
            }
        }
    }


    /**
     * Traverses through all child nodes recursively of the current node and checks if the node type exists in a given node type array and returns these nodes as an array
     * @param string[] $nodeTypes The target node to traverse through
     * @return NodeAbstract[]
     */
    public function getChildrenByNodeType(array $nodeTypes): array {

        $nodeList = [];

        $this -> getChildrenRecursive(function($node) use ($nodeTypes, &$nodeList) {

            if(true === in_array(get_class($node), $nodeTypes, true)) {
                $nodeList[$node -> getObjectId()] = $node;
            }
        });

        return $nodeList;
    }
}