<?php
declare(strict_types=1);

namespace sFire\Dom;

use sFire\Dom\Interpreter\Attribute\Attribute;
use sFire\Dom\NodeManager\Interfaces\AttributeInterface;
use sFire\Dom\NodeManager\Interfaces\ParentInterface;
use sFire\Dom\NodeManager\Interfaces\TagInterface;
use sFire\Dom\NodeManager\Nodes\CdataNode;
use sFire\Dom\NodeManager\Nodes\CommentNode;
use sFire\Dom\NodeManager\Nodes\DocTypeNode;
use sFire\Dom\NodeManager\Nodes\NodeAbstract;
use sFire\Dom\NodeManager\Nodes\OpeningNode;
use sFire\Dom\NodeManager\Nodes\PrologNode;
use sFire\Dom\NodeManager\Nodes\RootNode;
use sFire\Dom\NodeManager\Nodes\SelfClosingNode;
use sFire\Dom\NodeManager\Nodes\TextNode;


class DomNode {


    public const NODE_TYPE_CDATA = 'cdata';
    public const NODE_TYPE_COMMENT = 'comment';
    public const NODE_TYPE_DOCTYPE = 'doctype';
    public const NODE_TYPE_NODE = 'node';
    public const NODE_TYPE_PROLOG = 'prolog';
    public const NODE_TYPE_TEXT = 'text';
    public const NODE_TYPE_UNKNOWN = 'unknown';


    /**
     * Contains an instance of NodeAbstract
     * @var NodeAbstract
     */
    private NodeAbstract $node;


    /**
     * Contains an instance of DomParser
     * @var DomParser
     */
    private DomParser $domParser;


    /**
     * Constructor
     * @param NodeAbstract $node
     * @param DomParser $domParser
     */
    public function __construct(NodeAbstract $node, DomParser $domParser) {

        $this -> node = $node;
        $this -> domParser = $domParser;
    }


    /**
     * Returns the tag name of the current node if exists
     * @return null|string
     */
    public function getTagName(): ?string {

        if($this -> node instanceof TagInterface) {
            return $this -> node -> getTagName();
        }

        return null;
    }


    /**
     * Returns the unique id for the current node
     * @return int
     */
    public function getNodeId(): int {
        return $this -> node -> getObjectId();
    }


    /**
     * Return a new instance of DomNode with the parent of the current node if exists
     * @return null|DomNode
     */
    public function getParent(): ?DomNode {

        $parent = $this -> node -> getParent();

        if($parent instanceof NodeAbstract && false === $parent instanceof RootNode) {
            return new self($parent, $this -> domParser);
        }

        return null;
    }


    /**
     * Returns all the children nodes of current node
     * @return DomNodes
     */
    public function getChildren(): DomNodes {

        if($this -> node instanceof ParentInterface) {
            return new DomNodes($this -> node -> getChildren(), $this -> domParser);
        }

        return new DomNodes([], $this -> domParser);
    }


    /**
     * Returns an array with all found text within the current node and its children nodes recursively
     * @return string[]
     */
    public function getText(): array {

        $text = [];

        if($this -> node instanceof ParentInterface) {

            $textNodes = $this -> node -> getChildrenByNodeType([TextNode::class]);

            foreach($textNodes as $textNode) {

                $content = trim($textNode -> getContent());

                if($content !== '') {
                    $text[] = $content;
                }
            }
        }

        return $text;
    }


    /**
     * Returns the current node's type
     * @return string
     */
    public function getType(): string {
        return $this -> node -> getType();
    }


    /**
     * Returns an instance of DomNodes with all found comment nodes within the current node and its children nodes recursively
     * @return DomNodes
     */
    public function filterCommentNodes(): DomNodes {

        $nodes = true === $this -> node instanceof ParentInterface ? $this -> node -> getChildrenByNodeType([CommentNode::class]) : [];
        return new DomNodes($nodes, $this -> domParser);
    }


    /**
     * Returns an instance of DomNodes with all found text nodes within the current node and its children nodes recursively
     * @return DomNodes
     */
    public function filterTextNodes(): DomNodes {

        $nodes = true === $this -> node instanceof ParentInterface ? $this -> node -> getChildrenByNodeType([TextNode::class]) : [];
        return new DomNodes($nodes, $this -> domParser);
    }


    /**
     * Returns an instance of DomNodes with all found prolog nodes within the current node and its children nodes recursively
     * @return DomNodes
     */
    public function filterPrologNodes(): DomNodes {

        $nodes = true === $this -> node instanceof ParentInterface ? $this -> node -> getChildrenByNodeType([PrologNode::class]) : [];
        return new DomNodes($nodes, $this -> domParser);
    }


    /**
     * Returns an instance of DomNodes with all found CData nodes within the current node and its children nodes recursively
     * @return DomNodes
     */
    public function filterCdataNodes(): DomNodes {

        $nodes = true === $this -> node instanceof ParentInterface ? $this -> node -> getChildrenByNodeType([CdataNode::class]) : [];
        return new DomNodes($nodes, $this -> domParser);
    }


    /**
     * Returns an instance of DomNodes with all found other nodes within the current node and its children nodes recursively
     * @return DomNodes
     */
    public function filterParentNodes(): DomNodes {

        $nodes = true === $this -> node instanceof ParentInterface ? $this -> node -> getChildrenByNodeType([RootNode::class, OpeningNode::class, SelfClosingNode::class]) : [];
        return new DomNodes($nodes, $this -> domParser);
    }


    /**
     * Returns an instance of DomNodes with all found Doctype nodes within the current node and its children nodes recursively
     * @return DomNodes
     */
    public function filterDocTypeNodes(): DomNodes {

        $nodes = true === $this -> node instanceof ParentInterface ? $this -> node -> getChildrenByNodeType([DocTypeNode::class]) : [];
        return new DomNodes($nodes, $this -> domParser);
    }


    /**
     * Returns if the current node has children nodes or not
     * @return bool
     */
    public function hasChildren(): bool {
        return $this -> node instanceof ParentInterface && count($this -> node -> getChildren()) > 0;
    }


    /**
     * Replaces the current node with the provided new content and returns the new node
     * @param self|string $content This can be a string or a instance of DomNodes
     * @return self
     */
    public function replace($content): self {

        $this -> createDomNodesInstance($content) -> replace($content) -> get();
        return $this;
    }


    /**
     * Inserts provided content to the end of the current node
     * @param self|string $content This can be a string or a instance of DomNodes
     * @return self
     */
    public function append($content): self {

        $this -> createDomNodesInstance($content) -> append($content);
        return $this;
    }


    /**
     * Inserts provided content to the beginning of the current node
     * @param self|string $content This can be a string or a instance of DomNodes
     * @return self
     */
    public function prepend($content): self {

        $this -> createDomNodesInstance($content) -> prepend($content);
        return $this;
    }


    /**
     * Filters the current node with the direct following sibling of the current node
     * @return self
     */
    public function next(): ?self {

        $nextNode = $this -> node -> next();

        if(null !== $nextNode) {
            return new self($nextNode, $this -> domParser);
        }

        return null;
    }


    /**
     * Filters the current node with all siblings after each the node
     * @return DomNodes
     */
    public function nextAll(): DomNodes {
        return $this -> createDomNodesInstance() -> nextAll();
    }


    /**
     * Filters the current node with the direct previous sibling of the current node
     * @return self
     */
    public function previous(): ?self {

        $previous = $this -> node -> previous();

        if(null !== $previous) {
            return new self($previous, $this -> domParser);
        }

        return null;
    }


    /**
     * Filters the current node with all siblings before the current node
     * @return DomNodes
     */
    public function previousAll(): DomNodes {
        return $this -> createDomNodesInstance() -> previousAll();
    }


    /**
     * Filters the current node with the siblings of all previous and next nodes of the current node
     * @return DomNodes
     */
    public function siblings(): DomNodes {
        return $this -> createDomNodesInstance() -> siblings();
    }


    /**
     * Filters the current node with all siblings before each the node
     * @return DomNodes
     */
    public function siblingsAll(): DomNodes {
        return $this -> createDomNodesInstance() -> siblingsAll();
    }


    /**
     * Inserts new content as a previous sibling for the current node
     * @param self|string $content This can be a string or a instance of DomNodes
     * @return self
     */
    public function insertBefore($content): self {

        $this -> createDomNodesInstance($content) -> insertBefore($content);
        return $this;
    }


    /**
     * Inserts new content as a next sibling for the current node
     * @param self|string $content This can be a string or a instance of DomNodes
     * @return self
     */
    public function insertAfter($content): self {

        $this -> createDomNodesInstance($content) -> insertAfter($content);
        return $this;
    }


    /**
     * Returns the inner (parsed) content of the node excluding it's own tag and attributes
     * @return null|string
     */
    public function getContent(): ?string {

        $stack = [];

        if($this -> node instanceof ParentInterface && true === $this -> node -> canHaveChildren()) {

            foreach($this -> node -> getChildren() as $child) {
                $stack[] = $child -> render();
            }
        }
        elseif($this -> node instanceof TextNode) {
            $stack[] = $this -> node -> render();
        }

        return count($stack) > 0 ? implode('', $stack) : null;
    }


    /**
     * Returns the outer (parsed) content of the node (including it's own tag and attributes
     * @return null|string
     */
    public function getOuterContent(): ?string {
        return $this -> node -> render();
    }


    /**
     * Returns an instance of Attribute which contains the type, key, value and enclosure type of the attribute based on an given attribute name
     * @param string $attributeName The name of the attribute
     * @param null|string $directiveName [optional] The directive name which is bound to the given attribute name
     * @return null|Attribute
     */
    public function getAttribute(string $attributeName, ?string $directiveName = null): ?Attribute {

        if($this -> node instanceof AttributeInterface) {
            return $this -> node -> getAttribute($attributeName, $directiveName);
        }

        return null;
    }


    /**
     * Returns an array with all the attributes as Attribute instances of the current node
     * @return Attribute[]
     */
    public function getAttributes(): array {

        if($this -> node instanceof AttributeInterface) {
            return $this -> node -> getAttributes();
        }

        return [];
    }


    /**
     * Returns an array with all the directive attributes as Attribute instances of the current node
     * @param null|string $groupBy
     * @return Attribute[]
     */
    public function getDirectives(string $groupBy = null): array {

        return array_filter($this -> getAttributes(), static function($attribute) use ($groupBy) {
            return (null === $groupBy && null !== $attribute -> getDirective()) || (null !== $groupBy && $groupBy === $attribute -> getDirective());
        });
    }


    /**
     * Removes an attribute from the current node based on a given attribute name
     * @param string $attributeName The name of the attribute
     * @return self
     */
    public function removeAttribute(string $attributeName): self {

        if($this -> node instanceof AttributeInterface) {
            $this -> node -> removeAttribute($attributeName);
        }

        return $this;
    }


    public function hasAttribute(string $attributeName): bool {

        if($this -> node instanceof AttributeInterface) {
            return $this -> node -> hasAttribute($attributeName);
        }

        return false;
    }


    /**
     * Removes a directive based on directive name. If second parameter $attributeName is given, only the attribute name which belongs to the directive will be removed
     * @param string $directiveName The name of the directive (i.e. bind:id="foo", bind:class="bar" where bind is the directive)
     * @param null|string $attributeName The name of the attribute which belongs to the given directive name
     * @return self
     */
    public function removeDirective(string $directiveName, string $attributeName = null): self {

        if($this -> node instanceof AttributeInterface) {
            $this -> node -> removeDirective($directiveName, $attributeName);
        }

        return $this;
    }


    /**
     * Adds a new an attribute based on a given attribute name
     * @param string $type The attribute name
     * @param string|null $value [optional] The value of the attribute
     * @param null|string $enclosureType [optional] The enclosure type of the attribute
     * @return self
     */
    public function setAttribute(string $type, ?string $value = null, ?string $enclosureType = '"'): self {

        if($this -> node instanceof AttributeInterface) {

            $this -> node -> removeAttribute($type);

            $attribute = new Attribute();
            $attribute -> setType($type);
            $attribute -> setValue($value);
            $attribute -> setEnclosureType($enclosureType);
            $this -> node -> addAttribute($attribute);
        }

        return $this;
    }


    /**
     * Adds a new an attribute based on a given attribute name
     * @param string $directive The name of the directive
     * @param string $type The attribute name
     * @param string|null $value [optional] The value of the attribute
     * @param null|string $enclosureType [optional] The enclosure type of the attribute
     * @return self
     */
    public function setDirective(string $directive, string $type, ?string $value = null, ?string $enclosureType = '"'): self {

        if($this -> node instanceof AttributeInterface) {

            $this -> node -> removeDirective($directive, $type);

            $attribute = new Attribute();
            $attribute -> setDirective($directive);
            $attribute -> setType($type);
            $attribute -> setValue($value);
            $attribute -> setEnclosureType($enclosureType);
            $this -> node -> addAttribute($attribute);
        }

        return $this;
    }


    /**
     * Returns the first parent node that matches the selector by testing the node itself and traversing up through its ancestors in the DOM tree
     * @param string $selector
     * @return null|self
     */
    public function closest(string $selector): ?self {

        $targetNode = $this -> node -> findParent($selector);

        if(null !== $targetNode) {
            return new DomNode($targetNode, $this -> domParser);
        }

        return null;
    }


    /**
     * Returns the descendants of the current node, filtered by a CSS selector
     * @param string $selector
     * @return DomNodes
     */
    public function find(string $selector): DomNodes {

        $nodes = true === $this -> node instanceof ParentInterface ? $this -> node -> findChildren($selector) : [];
        return new DomNodes($nodes, $this -> domParser);
    }


    /**
     * Returns if the current node contains nodes that represents a given CSS selector
     * @param string $selector
     * @return bool
     */
    public function contains(string $selector): bool {
        return $this -> find($selector) -> count() > 0;
    }


    /**
     * Removes the current node from the DOM
     * @return void
     */
    public function remove(): void {
        $this -> node -> remove();
    }


    /**
     * Removes all the children of the current node from the DOM
     * @return self
     */
    public function clear(): self {

        if($this -> node instanceof ParentInterface) {

            foreach($this -> node -> getChildren() as $child) {
                $this -> node -> removeChild($child);
            }
        }
        else {
            (new self($this -> node, $this -> domParser)) -> replace('');
        }

        return $this;
    }


    /**
     * Creates a deep copy of the current node
     * @return self
     */
    public function copy(): self {
        return new DomNode(clone($this -> node), $this -> domParser);
    }


    /**
     * @param null $content
     * @return DomNodes
     */
    private function createDomNodesInstance(&$content = null): DomNodes {

        if($content instanceof self) {
            $content = new DomNodes([$content -> node], $this -> domParser);
        }

        return (new DomNodes([$this -> node], $this -> domParser));
    }
}