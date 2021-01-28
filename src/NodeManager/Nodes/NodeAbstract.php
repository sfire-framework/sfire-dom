<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Nodes;

use sFire\Dom\Builder\BuilderAbstract;
use sFire\Dom\Interpreter\Css\CssInterpreter;
use sFire\Dom\NodeManager\Interfaces\ChildrenBehaviorInterface;
use sFire\Dom\NodeManager\Interfaces\IndexInterface;
use sFire\Dom\NodeManager\Interfaces\ParentInterface;
use sFire\Dom\NodeManager\Interfaces\TagInterface;


abstract class NodeAbstract implements ChildrenBehaviorInterface {


    /**
     * Contains the content of the node as a string
     * @var null|string
     */
    private ?string $content = null;


    /**
     * Contains a unique object id for the current node
     * @var int
     */
    private int $objectId;


    /**
     * Contains an instance of BuilderAbstract
     * @var BuilderAbstract
     */
    private BuilderAbstract $builder;


    /**
     * Contains an instance of OpeningNode which represent the parent of the node
     * @var NodeAbstract|null
     */
    private ?NodeAbstract $parent = null;


    /**
     * Constructor
     */
    public function __construct() {
        $this -> createObjectId();
    }


    /**
     * Creates a new object id for the node when the current node is cloned
     */
    public function __clone() {
        $this -> createObjectId();
    }


    /**
     * Renders the current node when the node it is treated like a string
     * @return string
     */
    public function __toString(): string {
        return $this -> render();
    }


    /**
     * Returns the type of the node
     * @return string
     */
    abstract public function getType(): string;


    /**
     * Sets the builder for the node
     * @param BuilderAbstract $builder
     * @return void
     */
    public function setBuilder(BuilderAbstract $builder): void {
        $this -> builder = $builder;
    }


    /**
     * Returns the builder for the node
     * @return BuilderAbstract
     */
    public function getBuilder(): BuilderAbstract {
        return $this -> builder;
    }


    /**
     * Returns the contents as a string of the node
     * @return string
     */
    public function render(): string {
        return $this -> getContent();
    }


    /**
     * Sets the content of the node
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void {
        $this -> content = $content;
    }


    /**
     * Returns the content as a string of the node
     * @return null|string
     */
    public function getContent(): ?string {
        return $this -> content;
    }


    /**
     * Sets the parent of the current node
     * @param ParentInterface $node The node that is the parent of the current node
     * @return void
     */
    public function setParent(ParentInterface $node): void {

        if($node instanceof self) {
            $this -> parent = $node;
        }
    }


    /**
     * Returns the parent of the current node if exists
     * @return null|NodeAbstract
     */
    public function getParent(): ?NodeAbstract {
        return $this -> parent;
    }
    
    
    
    public function findParent(string $selector): ?NodeAbstract {

        $cssSelector = new CssInterpreter($selector);
        $parents = [];

        $this -> getParentsRecursive(function(NodeAbstract $node) use (&$parents) {
            $parents[] = $node -> getObjectId();
        });

        $nodes = $this -> builder -> filter($cssSelector, $this -> builder -> getRootNode());
        $found = array_intersect($parents, array_keys($nodes));
        $parentObjectId = array_shift($found);

        return $nodes[$parentObjectId] ?? null;
    }


    public function next(): ?NodeAbstract {

        $parent = $this -> getParent();

        if(false === $parent instanceof ParentInterface) {
            return null;
        }

        $children = $parent -> getChildren();
        $previous = null;

        foreach(array_reverse($children) as $child) {

            if(false === $child instanceof TagInterface) {
                continue;
            }

            if($this === $child) {
                return $previous;
            }

            $previous = $child;
        }

        return null;
    }


    public function previous(): ?NodeAbstract {

        $parent = $this -> getParent();

        if(false === $parent instanceof ParentInterface) {
            return null;
        }

        $children = $parent -> getChildren();
        $previous = null;

        foreach($children as $child) {

            if(false === $child instanceof TagInterface) {
                continue;
            }

            if($this === $child) {
                return $previous;
            }

            $previous = $child;
        }

        return null;
    }


    public function getPositionInParent(array $excludesTypes = []): ?int {

        $parent = $this -> getParent();

        if(false === $parent instanceof ParentInterface) {
            return null;
        }

        $position = -1;

        foreach($parent -> getChildren() as $childNode) {

            if(true === in_array(get_class($childNode), $excludesTypes, true)) {
                continue;
            }

            $position++;

            if($this === $childNode) {
                return $position;
            }
        }

        return null;
    }


    public function isFirstOfType(): bool {

        if(false === $this instanceof TagInterface) {
            return true;
        }

        $parent = $this -> getParent();

        if(false === $parent instanceof ParentInterface) {
            return true;
        }

        $children = $parent -> getChildren();

        foreach($children as $child) {

            if(false === $child instanceof TagInterface) {
                continue;
            }

            if($child -> getTagName() === $this -> getTagName()) {
                return $this === $child;
            }
        }

        return false;
    }

    public function isLastOfType(): bool {

        if(false === $this instanceof TagInterface) {
            return true;
        }

        $parent = $this -> getParent();

        if(false === $parent instanceof ParentInterface) {
            return true;
        }

        $children = $parent -> getChildren();

        foreach(array_reverse($children) as $child) {

            if(false === $child instanceof TagInterface) {
                continue;
            }

            if($child -> getTagName() === $this -> getTagName()) {
                return $this === $child;
            }
        }

        return false;
    }


    /**
     * Returns true if the current node is the only child of it's parent (skipping the comment, CDATA and text node types)
     * @return bool
     */
    public function isOnlyChild(): bool {

        $parent = $this -> getParent();

        if(false === $parent instanceof ParentInterface) {
            return false;
        }

        return 1 === count(array_filter($parent -> getChildren(), static function($child) {
            return true === $child instanceof IndexInterface;
        }));
    }


    /**
     * Returns if the current node has a parent
     * @return bool
     */
    public function hasParent(): bool {
        return null !== $this -> parent;
    }


    /**
     * Removes the current node from the parent/root
     * @return void
     */
    public function remove(): void {

        if($this -> parent instanceof ParentInterface) {
            $this -> parent -> removeChild($this);
        }
    }


    /**
     * Returns the unique object id for the current node
     * @return int
     */
    public function getObjectId(): int {
        return $this -> objectId;
    }


    /**
     * Creates a unique object id for the current node
     * @return void
     */
    private function createObjectId(): void {
        $this -> objectId = spl_object_id($this);
    }


    /**
     * Executes a provided callback for each parent of a given target node traversing up to the root node
     * @param callable $callback
     * @param NodeAbstract|null $targetNode
     * @return void
     */
    public function getParentsRecursive(callable $callback, NodeAbstract $targetNode = null): void {

        $targetNode = $targetNode ?? $this;

        if($targetNode instanceof ParentInterface) {

            $parent = $targetNode -> getParent();

            if(null === $parent) {
                return;
            }

            $callback($parent);

            if($parent instanceof ParentInterface) {
                $this -> getParentsRecursive($callback, $parent);
            }
        }
    }


    /**
     * Returns an array for each parent of the current node traversing up to the root node
     * @return int[]
     */
    public function getParentNodeIds(): array {

        $ids = [];

        $this -> getParentsRecursive(function(NodeAbstract $node) use (&$ids) {
            $ids[] = $node -> getObjectId();
        });

        return $ids;
    }
}