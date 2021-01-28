<?php
declare(strict_types=1);

namespace sFire\Dom;

use BadMethodCallException;
use Countable;
use sFire\Dom\Interpreter\Attribute\Attribute;
use sFire\Dom\NodeManager\Interfaces\AttributeInterface;
use sFire\Dom\NodeManager\Interfaces\ParentInterface;
use sFire\Dom\NodeManager\Nodes\CdataNode;
use sFire\Dom\NodeManager\Nodes\CommentNode;
use sFire\Dom\NodeManager\Nodes\DocTypeNode;
use sFire\Dom\NodeManager\Nodes\NodeAbstract;
use sFire\Dom\NodeManager\Nodes\OpeningNode;
use sFire\Dom\NodeManager\Nodes\PrologNode;
use sFire\Dom\NodeManager\Nodes\RootNode;
use sFire\Dom\NodeManager\Nodes\SelfClosingNode;
use sFire\Dom\NodeManager\Nodes\TextNode;


class DomNodes implements Countable {


    /**
     * Contains all matches nodes
     * @var NodeAbstract[]
     */
    private array $nodes;


    /**
     * Contains an instance of DomParser
     * @var DomParser
     */
    private DomParser $domParser;


    /**
     * Constructor
     * @param array $nodes
     * @param DomParser $domParser
     */
    public function __construct(array $nodes, DomParser $domParser) {

        $this -> nodes = $nodes;
        $this -> domParser = $domParser;
    }


    /**
     * Returns the a single node by a given index from the set of matched nodes
     * @param int $index
     * @return null|DomNode
     */
    public function get(int $index = 0): ?DomNode {

        $nodes = array_values($this -> nodes);
        return true === isset($nodes[$index]) ? new DomNode($nodes[$index], $this -> domParser) : null;
    }


    /**
     * Returns the amount of matched nodes
     * @return int
     */
    public function count(): int {
        return count($this -> nodes);
    }


    /**
     * Iterates over all the matched nodes, executing a function for each node
     * @param callable $callable
     * @return self
     */
    public function each(callable $callable): self {

        foreach($this -> nodes as $index => $node) {
            $callable(new DomNode($node, $this -> domParser), $index, $this -> domParser);
        }

        return $this;
    }


    /**
     * Returns if one or more of the selected nodes has children nodes
     * @return bool
     */
    public function hasChildren(): bool {

        foreach($this -> nodes as $node) {

            $hasChildren = (new DomNode($node, $this -> domParser)) -> hasChildren();

            if(true === $hasChildren) {
                return true;
            }
        }

        return false;
    }


    /**
     * Replaces each node in the set of matched nodes with the provided new content
     * @param self|string $content This can be a string or a instance of DomNodes
     * @return self
     */
    public function replace($content): self {

        if(true === $content instanceof DomNode) {

            foreach($this -> nodes as $node) {
                (new DomNode($node, $this -> domParser)) -> replace($content);
            }

            return $this;
        }

        return $this -> contentNodes($content, static function(NodeAbstract $node, array $contentNodes) {

            $parent = $node -> getParent();

            if($parent instanceof ParentInterface) {

                foreach($contentNodes as $contentNode) {

                    $contentNode = clone($contentNode);
                    $parent -> insertChildBefore($contentNode, $node);
                }

                $node -> remove();
            }
        });
    }


    /**
     * Inserts every node in the set of matched nodes after the target
     * @param self|string $content This can be a string or a instance of DomNodes
     * @return self
     */
    public function insertAfter($content): self {

        if(true === $content instanceof DomNode) {

            foreach($this -> nodes as $node) {
                (new DomNode($node, $this -> domParser)) -> insertAfter($content);
            }

            return $this;
        }

        return $this -> contentNodes($content, static function(NodeAbstract $node, array $contentNodes) {

            $parent = $node -> getParent();

            if($parent instanceof ParentInterface) {

                foreach(array_reverse($contentNodes) as $contentNode) {

                    $contentNode = clone($contentNode);
                    $parent -> insertChildAfter($contentNode, $node);
                }
            }
        });
    }


    /**
     * Removes a single attribute by name in the set of matched nodes
     * @param string $attributeName The name of the attribute
     * @return self
     */
    public function removeAttribute(string $attributeName): self {

        foreach($this -> nodes as $node) {

            if($node instanceof AttributeInterface) {
                $node -> removeAttribute($attributeName);
            }
        }

        return $this;
    }


    /**
     * Inserts every node in the set of matched nodes before the target
     * @param self|string $content This can be a string or a instance of DomNodes
     * @return self
     */
    public function insertBefore($content): self {

        if(true === $content instanceof DomNode) {

            foreach($this -> nodes as $node) {
                (new DomNode($node, $this -> domParser)) -> insertBefore($content);
            }

            return $this;
        }

        return $this -> contentNodes($content, static function(NodeAbstract $node, array $contentNodes) {

            $parent = $node -> getParent();

            if($parent instanceof ParentInterface) {

                foreach($contentNodes as $contentNode) {

                    $contentNode = clone($contentNode);
                    $parent -> insertChildBefore($contentNode, $node);
                }
            }
        });
    }


    /**
     * Inserts content, to the end of each node in the set of matched nodes
     * @param self|string $content This can be a string or a instance of DomNodes
     * @return self
     */
    public function append($content): self {
        return $this -> add($content);
    }


    /**
     * Inserts content, to the beginning of each node in the set of matched nodes
     * @param self|string $content This can be a string or a instance of DomNodes
     * @return self
     */
    public function prepend($content): self {
        return $this -> add($content, true);
    }


    /**
     * Filters the current matched nodes with the immediately following sibling of each node
     * @return self
     */
    public function next(): self {

        $nodeList = [];

        foreach($this -> nodes as $node) {

            $nextNode = $node -> next();

            if(null !== $nextNode) {
                $nodeList[$nextNode -> getObjectId()] = $nextNode;
            }
        }

        $this -> nodes = $nodeList;
        return $this;
    }


    /**
     * Filters the current matched nodes with all siblings after each current node
     * @return self
     */
    public function nextAll(): self {

        $nodeList = [];

        foreach($this -> nodes as $node) {

            while($node = $node -> next()) {
                $nodeList[$node -> getObjectId()] = $node;
            }
        }

        $this -> nodes = $nodeList;
        return $this;
    }


    /**
     * Filters the current matched nodes with the immediately previous sibling of each node
     * @return self
     */
    public function previous(): self {

        $nodeList = [];

        foreach($this -> nodes as $node) {

            $previousNode = $node -> previous();

            if(null !== $previousNode) {
                $nodeList[$previousNode -> getObjectId()] = $previousNode;
            }
        }

        $this -> nodes = $nodeList;
        return $this;
    }


    /**
     * Filters the current matched nodes with all siblings before each current node
     * @return self
     */
    public function previousAll(): self {

        $nodeList = [];

        foreach($this -> nodes as $node) {

            while($node = $node -> previous()) {
                $nodeList[$node -> getObjectId()] = $node;
            }
        }

        $this -> nodes = $nodeList;
        return $this;
    }


    /**
     * Filters the current matched nodes with the direct siblings of each node
     * @return self
     */
    public function siblings(): self {

        $nodeList = [];

        foreach($this -> nodes as $node) {

            $previousNode = $node -> previous();
            $nextNode = $node -> next();

            if(null !== $previousNode) {
                $nodeList[$previousNode -> getObjectId()] = $previousNode;
            }

            if(null !== $nextNode) {
                $nodeList[$nextNode -> getObjectId()] = $nextNode;
            }
        }

        $this -> nodes = $nodeList;
        return $this;
    }


    /**
     * Filters the current matched nodes with all siblings before and after each current node
     * @return self
     */
    public function siblingsAll(): self {

        $nodeList = [];

        foreach($this -> nodes as $node) {

            $currentNode = $node;

            while($node = $node -> previous()) {
                $nodeList[$node -> getObjectId()] = $node;
            }

            while($currentNode = $currentNode -> next()) {
                $nodeList[$currentNode -> getObjectId()] = $currentNode;
            }
        }

        $this -> nodes = $nodeList;
        return $this;
    }


    /**
     * Returns an array with Attribute instances for the matched nodes
     * @param string $attributeName The name of the attribute
     * @return null|Attribute[]
     */
    public function getAttribute(string $attributeName): ?array {

        $attributes = [];

        foreach($this -> nodes as $node) {
            $attributes[] = (new DomNode($node, $this -> domParser)) -> getAttribute($attributeName);
        }

        return $attributes;
    }


    /**
     * Returns the key and value of every attributes of every matched nodes
     * @return array
     */
    public function getAttributes(): array {

        $attributes = [];

        foreach($this -> nodes as $node) {

            if(false === $node instanceof AttributeInterface) {
                continue;
            }

            $attributes[] = $node -> getAttributes();
        }

        return $attributes;
    }


    /**
     * Returns the first parent node that matches the selector by testing the node itself and traversing up through its ancestors in the DOM tree for each matched node
     * @param string $selector
     * @return null|self
     */
    public function closest(string $selector): self {

        $nodeList = [];

        foreach($this -> nodes as $node) {

            $targetNode = $node -> findParent($selector);

            if(null !== $targetNode) {
                $nodeList[$targetNode -> getObjectId()] = $targetNode;
            }
        }

        $this -> nodes = $nodeList;
        return $this;
    }


    /**
     * Returns the descendants for each matched node, filtered by a CSS selector
     * @param string $selector
     * @return DomNodes
     */
    public function find(string $selector): self {

        $nodeList = [];

        foreach($this -> nodes as $node) {

            if(true === $node instanceof ParentInterface) {
                $nodeList[] = $node -> findChildren($selector);
            }
        }

        $this -> nodes = array_replace([], ...$nodeList);
        return $this;
    }


    /**
     * Returns if a node contains nodes that represents a given CSS selector for each matched node
     * @param string $selector
     * @return bool
     */
    public function contains(string $selector): bool {
        return $this -> find($selector) -> count() > 0;
    }


    /**
     * Filters the current matched nodes with the direct parent of each node
     * @return self
     */
    public function getParents(): self {

        $nodeList = [];

        foreach($this -> nodes as $node) {

            $parent = $node -> getParent();

            if($parent instanceof OpeningNode) {
                $nodeList[$parent -> getObjectId()] = $parent;
            }
        }

        $this -> nodes = $nodeList;
        return $this;
    }


    /**
     * Removes the set of matched nodes from the DOM
     * @return void
     */
    public function remove(): void {

        foreach($this -> nodes as $node) {
            $node -> remove();
        }

        $this -> nodes = [];
    }


    /**
     * Returns an instance of DomNodes with all found text nodes
     * @return DomNodes
     */
    public function filterTextNodes(): DomNodes {
        return new DomNodes($this -> getNodesByType([TextNode::class]), $this -> domParser);
    }


    /**
     * Returns an instance of DomNodes with all found comment nodes
     * @return DomNodes
     */
    public function filterCommentNodes(): DomNodes {
        return new DomNodes($this -> getNodesByType([CommentNode::class]), $this -> domParser);
    }


    /**
     * Returns an instance of DomNodes with all found prolog nodes
     * @return DomNodes
     */
    public function filterPrologNodes(): DomNodes {
        return new DomNodes($this -> getNodesByType([PrologNode::class]), $this -> domParser);
    }


    /**
     * Returns an instance of DomNodes with all found CData nodes
     * @return DomNodes
     */
    public function filterCdataNodes(): DomNodes {
        return new DomNodes($this -> getNodesByType([CdataNode::class]), $this -> domParser);
    }


    /**
     * Returns an instance of DomNodes with all found other nodes
     * @return DomNodes
     */
    public function filterParentNodes(): DomNodes {
        return new DomNodes($this -> getNodesByType([RootNode::class, OpeningNode::class, SelfClosingNode::class]), $this -> domParser);
    }


    /**
     * Returns an instance of DomNodes with all found Doctype nodes
     * @return DomNodes
     */
    public function filterDocTypeNodes(): DomNodes {
        return new DomNodes($this -> getNodesByType([DocTypeNode::class]), $this -> domParser);
    }


    /**
     * Removes all the children of the set of matched nodes from the DOM
     * @return self
     */
    public function clear(): self {

        foreach($this -> nodes as $node) {
            (new DomNode($node, $this -> domParser)) -> clear();
        }

        return $this;
    }


    /**
     * Executes a callback method to apply a filter for the matched nodes
     * @param callable $callback
     * @param bool $groupByParent
     * @return self
     */
    public function filter(callable $callback, bool $groupByParent = false): self {

        $counter = 0;
        $nodeList = [];
        $currentParentId = null;

        foreach($this -> nodes as $node) {

            if(true === $groupByParent) {

                $parent = $node -> getParent();

                if(false === $parent instanceof NodeAbstract) {
                    continue;
                }

                $parentId = $parent -> getObjectId();

                if($parentId !== $currentParentId) {

                    $currentParentId = $parentId;
                    $counter = 0;
                }
            }

            if(true === $callback(new DomNode($node, $this -> domParser), $counter)) {
                $nodeList[$node -> getObjectId()] = $node;
            }

            $counter++;
        }

        $this -> nodes = $nodeList;
        return $this;
    }


    /**
     * Reduces the set of matched nodes to the first in the set
     * @param bool $groupByParent
     * @return self
     */
    public function first(bool $groupByParent = false): self {

        return $this -> filter(static function(DomNode $node, int $index) {
            return $index === 0;
        }, $groupByParent);
    }


    /**
     * Reduces the set of matched nodes to the last in the set
     * @param bool $groupByParent
     * @return self
     */
    public function last(bool $groupByParent = false): self {

        $this -> nodes = array_reverse($this -> nodes, true);

        $this -> filter(static function(DomNode $node, int $index) {
            return $index === 0;
        }, $groupByParent);

        $this -> nodes = array_reverse($this -> nodes, true);
        return $this;
    }


    /**
     * Reduces the set of matched nodes to the odd ones in the set, numbered from one.
     * @param bool $groupByParent
     * @return self
     */
    public function odd(bool $groupByParent = false): self {

        return $this -> filter(static function(DomNode $node, int $index) {
            return 0 === $index % 2;
        }, $groupByParent);
    }


    /**
     * Reduces the set of matched nodes to the event ones in the set, numbered from one.
     * @param bool $groupByParent
     * @return self
     */
    public function even(bool $groupByParent = false): self {

        return $this -> filter(static function(DomNode $node, int $index) {
            return 0 !== $index % 2;
        }, $groupByParent);
    }


    /**
     * Creates a deep copy of the set of matched nodes
     * @return self
     */
    public function copy(): self {

        $nodeList = [];

        foreach($this -> nodes as $node) {
            $nodeList[$node -> getObjectId()] = clone($node);
        }

        return new DomNodes($nodeList, $this -> domParser);
    }


    /**
     * Sets a new or overwrites an existing attribute for every matched node
     * @param string $key The name of the attribute
     * @param string|null $value The value of the attribute
     * @param null|string $enclosureType [optional] The enclosure type of the attribute
     * @return self
     */
    public function setAttribute(string $key, ?string $value = null, ?string $enclosureType = '"'): self {

        foreach($this -> nodes as $node) {
            (new DomNode($node, $this -> domParser)) -> setAttribute($key, $value, $enclosureType);
        }

        return $this;
    }


    /**
     * Sets a new or overwrites an existing attribute for every matched node
     * @param string $directive The name of the directive
     * @param string $key The name of the attribute
     * @param string|null $value The value of the attribute
     * @param null|string $enclosureType [optional] The enclosure type of the attribute
     * @return self
     */
    public function setDirective(string $directive, string $key, ?string $value = null, ?string $enclosureType = '"'): self {

        foreach($this -> nodes as $node) {
            (new DomNode($node, $this -> domParser)) -> setDirective($directive, $key, $value, $enclosureType);
        }

        return $this;
    }


    /**
     * Inserts content, to the beginning or end of each node in the set of matched nodes
     * @param self|string $content This can be a string or a instance of DomNodes
     * @param bool $prepend Content will be inserted at the beginning if set to true
     * @return self
     */
    private function add($content, bool $prepend = false): self {

        return $this -> contentNodes($content, static function(NodeAbstract $node, array $contentNodes) use ($prepend) {

            if($node instanceof ParentInterface && true === $node -> canHaveChildren()) {

                foreach($contentNodes as $contentNode) {

                    $contentNode = clone($contentNode);
                    $prepend ? $node -> prependChild($contentNode) : $node -> appendChild($contentNode);
                }
            }
        });
    }


    /**
     * Extracts all nodes from the given content and iterates over each node in the set of matched nodes while calling the given callback function injecting the current node
     * @param self|string $content This can be a string or a instance of DomNodes
     * @param callable $callback
     * @return self
     */
    private function contentNodes($content, callable $callback): self {

        $contentNodes = null;

        if($content instanceof self) {
            $contentNodes = $content -> nodes;
        }
        elseif(true === is_string($content)) {
            $contentNodes = (new DomParser($content, $this -> domParser -> getType())) -> root() -> nodes;
        }

        if(null === $contentNodes) {
            throw new BadMethodCallException(sprintf('Expects parameter 1 to be instance of "%s" or string', __CLASS__));
        }

        foreach($this -> nodes as $node) {
            $callback($node, $contentNodes);
        }

        return $this;
    }


    /**
     * Returns an array with nodes filters by a given array with fully qualified namespaces FQN
     * @param array $nodeTypes
     * @return array
     */
    private function getNodesByType(array $nodeTypes): array {

        $nodeList = [];

        foreach($this -> nodes as $node) {

            if($node instanceof ParentInterface) {
                $nodeList[] = $node -> getChildrenByNodeType($nodeTypes);
            }
        }

        return array_merge(...array_values($nodeList));
    }
}