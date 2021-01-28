<?php
declare(strict_types=1);

namespace sFire\Dom\Builder;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use sFire\Dom\Interpreter\Attribute\Attribute;
use sFire\Dom\Interpreter\Css\Selectors\Attribute as AttributeSelector;
use sFire\Dom\Interpreter\Css\Selectors\Selector;
use sFire\Dom\NodeManager\Interfaces\IndexInterface;
use sFire\Dom\NodeManager\Interfaces\ParentInterface;
use sFire\Dom\NodeManager\Nodes\CommentNode;
use sFire\Dom\NodeManager\Nodes\NodeAbstract;
use sFire\Dom\NodeManager\Nodes\OpeningNode;
use sFire\Dom\NodeManager\Nodes\TextNode;


class Indexer {


    public const CSS_ATTRIBUTE_POWER = '^=';
    public const CSS_ATTRIBUTE_PIPE = '|=';
    public const CSS_ATTRIBUTE_DOLLAR = '$=';
    public const CSS_ATTRIBUTE_ASTERISK = '*=';
    public const CSS_ATTRIBUTE_TILDE = '~=';
    public const CSS_ATTRIBUTE_EQUAL = '=';


    /**
     * Contains all the tag names from all the nodes
     * @var array
     */
    public array $tags = [];


    /**
     * Contains all the attributes from all the nodes
     * @var array
     */
    public array $attributes = [];


    /**
     * Contains all the attribute directives from all the nodes
     * @var array
     */
    public array $directives = [];


    /**
     * Contains all nodes
     * @var IndexInterface[]
     */
    public array $nodes = [];


    /**
     * Contains paths to attributes for searching attributes lookups improvements
     * @var array
     */
    public array $attributePaths = [];


    /**
     * Contains paths to directives for searching directives lookups improvements
     * @var array
     */
    public array $directivePaths = [];


    /**
     * Add a instance of NodeAbstract which implements the IndexInterface to the current index
     * @param IndexInterface $node
     * @return void
     */
    public function addNode(IndexInterface $node): void {

        $id = $node -> getObjectId();

        if(true === isset($this -> nodes[$id])) {
            return;
        }

        if($node instanceof OpeningNode && true === $node -> canHaveChildren()) {

            foreach($node -> getChildren() as $child) {

                if($child instanceof IndexInterface) {
                    $this -> addNode($child);
                }
            }
        }

        $tagName = $node -> getTagName();
        $this -> nodes[$id] = $node;
        $this -> tags[$tagName] ??= [];

        if(false === isset($this -> tags[$tagName][$id])) {
            $this -> tags[$tagName][$id] = $id;
        }

        $this -> indexAttributes($node);
    }


    /**
     * Returns node object ids based on node tag names
     * @param string $tagName The tag name to look for
     * @return int[]
     */
    public function getNodeObjectIdsByTagName(string $tagName): array {
        return $this -> tags[$tagName] ?? [];
    }


    /**
     * Returns node object ids based on node class names
     * @param string $className The class name to look for
     * @return int[]
     */
    public function getNodeObjectIdsByClassName(string $className): array {
        return $this -> attributes['class'][$className] ?? [];
    }


    /**
     * Returns node object ids based on node id attribute
     * @param string $id The class name to look for
     * @return int[]
     */
    public function getNodeObjectIdsByAttributeId(string $id): array {
        return $this -> attributes['id'][$id] ?? [];
    }


    /**
     * Returns node object ids based on an instance of Attribute
     * @param AttributeSelector $attribute
     * @return int[]
     */
    public function getNodeObjectIdsByAttribute(AttributeSelector $attribute): array {

        $matchedNodes = [];
        $indexSource = $this -> attributes[$attribute -> getType()] ?? [];

        if(null !== $attribute -> getDirective()) {
            $indexSource = null === $attribute -> getType() ? $this -> directives[$attribute -> getDirective()] ?? [] : $this -> directives[$attribute -> getDirective()][$attribute -> getType()] ?? [];
        }

        if(null === $attribute -> getType()) {
            return iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($indexSource)));
        }

        foreach($indexSource as $value => $nodes) {

            if(true === $this -> matchAttribute($attribute, $value)) {
                $matchedNodes[] = $nodes;
            }
        }

        return array_merge([], ...$matchedNodes);
    }


    /**
     * Returns an IndexInterface instance based on the node object id
     * @param int $objectId The spl_object_id() from a IndexInterface instance
     * @return null|IndexInterface
     */
    public function getNodeByObjectId(int $objectId): ?IndexInterface {
        return $this -> nodes[$objectId] ?? null;
    }


    /**
     * Returns an array with nodes by a given instance of SelectorGroup
     * @param Selector $selector
     * @return NodeAbstract[]
     */
    public function getNodesBySelector(Selector $selector): array {

        $nodes = [];
        $nodeObjectIds = $this -> getNodeObjectIdsBySelector($selector);

        foreach($nodeObjectIds as $nodeObjectId) {

            $node = $this -> getNodeByObjectId($nodeObjectId);

            if(null !== $node) {
                $nodes[$nodeObjectId] = $node;
            }
        }

        return $nodes;
    }


    /**
     * Returns an array with node object ids by a given instance of Selector
     * @param Selector $selector
     * @return int[]
     */
    public function getNodeObjectIdsBySelector(Selector $selector): array {

        $nodes = [];
        $this -> fillNodesByTagName($selector, $nodes);
        $this -> fillNodesById($selector, $nodes);
        $this -> fillNodesByClassNames($selector, $nodes);
        $this -> fillNodesByAttributes($selector, $nodes);

        //Find all the matching nodes
        $objectIds = count($nodes) > 1 ? array_intersect(...array_values($nodes)) : array_merge(...array_values($nodes));

        //Filter the found node object ids with CSS pseudo selector
        $this -> fillNodesByPseudo($selector, $objectIds);

        return $objectIds;
    }


    public function indexAttribute(IndexInterface $targetNode, Attribute $attribute): void {

        $id        = $targetNode -> getObjectId();
        $directive = $attribute -> getDirective();
        $type      = $attribute -> getType();
        $value     = $attribute -> getValue();

        $this -> removeAttributeIndexByNode($targetNode, $attribute);

        if(null === $directive && null !== $type) {

            $values = 'class' === $type ? preg_split('/[\s]+/', (string) $value) : [$value];

            foreach($values as $value) {

                $this -> attributes[$type] ??= [];
                $this -> attributes[$type][$value] ??= [];

                if(false === in_array($id, $this -> attributes[$type][$value], true)) {

                    $this -> attributes[$type][$value][$id] = $id;
                    $this -> attributePaths[$id] ??= [];
                    $this -> attributePaths[$id][$id . $type . $value] = [$type, $value, $id];
                }
            }
        }

        if(null !== $directive) {

            $this -> directives[$directive] ??= [];
            $this -> directives[$directive][$type] ??= [];
            $this -> directives[$directive][$type][$value] ??= [];

            if(false === in_array($id, $this -> directives[$directive], true)) {

                $this -> directives[$directive][$type][$value][$id] = $id;
                $this -> directivePaths[$id] ??= [];
                $this -> directivePaths[$id][$id . $directive . $type . $value] = [$directive, $type, $value, $id];
            }
        }
    }


    /**
     * Tests a given value by a given instance of Attribute and returns if it matches or not
     * @param AttributeSelector $attribute
     * @param string|null $value
     * @return bool
     */
    private function matchAttribute(AttributeSelector $attribute, ?string $value): bool {

        switch($attribute -> getOperator()) {

            //Begins with
            case self::CSS_ATTRIBUTE_PIPE:
            case self::CSS_ATTRIBUTE_POWER:
                return 0 === strpos($value, $attribute -> getValue());

            //Ends with
            case self::CSS_ATTRIBUTE_DOLLAR:
                return $attribute -> getValue() === substr($value, -strlen($attribute -> getValue()));

            //Contains
            case self::CSS_ATTRIBUTE_ASTERISK:
                return false !== strpos($value, $attribute -> getValue());

            //Contains as whole word
            case self::CSS_ATTRIBUTE_TILDE:
                return true === (bool) preg_match('/\b'. preg_quote($attribute -> getValue(), '/') .'\b/i', $value);

            //Equals to
            case self::CSS_ATTRIBUTE_EQUAL:
                return (string) $value === (string) $attribute -> getValue();

            default:
                return true;
        }
    }


    /**
     * Indexes a node attributes
     * @param IndexInterface $targetNode
     * @return void
     */
    public function indexAttributes(IndexInterface $targetNode): void {

        foreach($targetNode -> getAttributes() as $attribute) {
            $this -> indexAttribute($targetNode, $attribute);
        }
    }


    /**
     * Removes a given node from the index
     * @param IndexInterface $targetNode
     * @return void
     */
    public function removeByNode(IndexInterface $targetNode): void {

        unset($this -> nodes[$targetNode -> getObjectId()], $this -> tags[$targetNode -> getTagName()][$targetNode -> getObjectId()]);
        $this -> removeAttributesIndexByNode($targetNode);
    }


    /**
     * Remove keys and values form an associative array by giving a flat key structure as array
     * @param array $keys A flat
     * @param null|array $data
     * @return mixed
     */
    private function removeFromKeys(array $keys, ?array &$data) {

        $current = array_shift($keys);
        $end  	 = count($keys) === 0;

        if(true === $end) {
            unset($data[$current]);
        }

        if(false === isset($data[$current])) {
            return null;
        }

        return $this -> removeFromKeys($keys, $data[$current]);
    }


    /**
     * Removes the attribute and directive path indexes by a given target node
     * @param IndexInterface $targetNode
     * @param Attribute $attribute
     * @return void
     */
    public function removeAttributeIndexByNode(IndexInterface $targetNode, Attribute $attribute): void {

        $id         = $targetNode -> getObjectId();
        $type       = $attribute -> getType();
        $value      = $attribute -> getValue();
        $directive  = $attribute -> getDirective();
        $path       = $this -> directivePaths[$id][$id . $directive . $type . $value] ?? null;

        if(null !== $path) {
            $this -> removeFromKeys($path, $this -> directives);
        }

        $path = $this -> attributePaths[$id][$id . $type . $value] ?? null;

        if(null !== $path) {
            $this -> removeFromKeys($path, $this -> attributes);
        }

        unset($this -> directivePaths[$id][$id . $directive . $type . $value], $this -> attributePaths[$id][$id . $type . $value]);
    }


    /**
     * Removes the attribute and directive paths indexes by a given target node
     * @param IndexInterface $targetNode
     * @return void
     */
    private function removeAttributesIndexByNode(IndexInterface $targetNode): void {

        $id = $targetNode -> getObjectId();

        foreach($this -> attributePaths[$id] ?? [] as $paths) {
            $this -> removeFromKeys($paths, $this -> attributes);
        }

        foreach($this -> directivePaths[$id] ?? [] as $paths) {
            $this -> removeFromKeys($paths, $this -> directives);
        }

        unset($this -> attributePaths[$id], $this -> directivePaths[$id]);
    }


    private function fillNodesByClassNames(Selector $selector, array &$nodes): void {

        //Get class names
        foreach($selector -> getClassNames() as $className) {
            $nodes[uniqid('class', true)] = $this -> getNodeObjectIdsByClassName($className -> getContent());
        }
    }

    private function fillNodesByAttributes(Selector $selector, array &$nodes): void {

        //Match attributes
        foreach($selector -> getAttributes() as $attribute) {
            $nodes[uniqid('attr', true)] = $this -> getNodeObjectIdsByAttribute($attribute);
        }
    }


    private function fillNodesByTagName(Selector $selector, array &$nodes): void {

        $tagName = ($tagName = $selector -> getTag()) ? $tagName -> getContent() : null;

        if(null !== $tagName) {
            $nodes['tag'] = $this -> getNodeObjectIdsByTagName($tagName);
        }
    }

    private function fillNodesById(Selector $selector, array &$nodes): void {

        $id = ($id = $selector -> getId()) ? $id -> getContent() : null;

        if(null !== $id) {
            $nodes['tag'] = $this -> getNodeObjectIdsByAttributeId($id);
        }
    }

    private function fillNodesByPseudo(Selector $selector, array &$objectIds): void {

        $pseudo = ($item = $selector -> getPseudo()) ? $item : null;

        if(null === $pseudo) {
            return;
        }

        $pseudoObjectIds = [];

        foreach($objectIds as $index => $nodeObjectId) {

            $node = $this -> getNodeByObjectId($nodeObjectId);

            if(null === $node) {
                continue;
            }

            switch($pseudo -> getType()) {

                case 'first-child':

                    if(false === $node instanceof NodeAbstract) {
                        continue 2;
                    }

                    $parent = $node -> getParent();

                    if(true === $parent instanceof ParentInterface) {

                        $childNode = $parent -> getFirstChild([TextNode::class, CommentNode::class]);

                        if($node === $childNode) {
                            $pseudoObjectIds[] = $node -> getObjectId();
                        }
                    }

                    break;

                case 'last-child':

                    if(false === $node instanceof NodeAbstract) {
                        continue 2;
                    }

                    $parent = $node -> getParent();

                    if(true === $parent instanceof ParentInterface) {

                        $childNode = $parent -> getLastChild([TextNode::class, CommentNode::class]);

                        if(null !== $childNode) {
                            $pseudoObjectIds[] = $childNode -> getObjectId();
                        }
                    }

                    break;

                case 'first-of-type':

                    if(false === $node instanceof NodeAbstract) {
                        continue 2;
                    }

                    if(true === $node -> isFirstOfType()) {
                        $pseudoObjectIds[] = $node -> getObjectId();
                    }

                    break;

                case 'last-of-type':

                    if(false === $node instanceof NodeAbstract) {
                        continue 2;
                    }

                    if(true === $node -> isLastOfType()) {
                        $pseudoObjectIds[] = $node -> getObjectId();
                    }

                    break;

                case 'only-child':

                    if(false === $node instanceof NodeAbstract) {
                        continue 2;
                    }

                    if(true === $node -> isOnlyChild()) {
                        $pseudoObjectIds[] = $node -> getObjectId();
                    }

                    break;

                case 'lang(language)': break;
                case 'not(selector)': break;
                case 'nth-child(n)': break;
                case 'nth-last-child(n)': break;
                case 'nth-last-of-type(n)': break;
                case 'nth-of-type(n)': break;
                case 'only-of-type': break;
            }
        }

        $objectIds = $pseudoObjectIds;
    }
}