<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Dom\Elements;

use sFire\Dom\Tags\Tag;
use sFire\Dom\Tags\Attribute;


/**
 * Class Node
 * @package sFire\Template\Dom
 */
class Node extends DomElementAbstract {


    /**
     * Contains the tag
     * @var null|Tag
     */
    private ?Tag $tag = null;


    /**
     * Contains all the child nodes
     * @var Node[]
     */
    private array $children = [];


    /**
     * Constructor
     * @param Tag $tag Instance of Tag
     */
    public function __construct(Tag $tag) {
        $this -> tag = $tag;
    }


    /**
     * Returns the tag
     * @return null|Tag
     */
    public function getTag(): ?Tag {
        return $this -> tag;
    }


    /**
     * Adds a child node to the current node
     * @param DomElementAbstract $node
     * @return void
     */
    public function addChild(DomElementAbstract $node): void {
        $this -> children[] = $node;
    }


    /**
     * Returns if the node has child nodes
     * @return bool
     */
    public function hasChildren(): bool {
        return count($this -> children) > 0;
    }


    /**
     * Returns all the child nodes in an array
     * @return DomElementAbstract[]
     */
    public function getChildren(): array {
        return $this -> children;
    }


    /**
     * Check if a child node already exists or not
     * @param DomElementAbstract $node
     * @return bool
     */
    public function childExists(DomElementAbstract $node): bool {
        return true === in_array($node, $this -> children);
    }


    /**
     * Returns whenever the node has attributes or not
     * @return bool
     */
    public function hasAttributes(): bool {

        if(null === $this -> tag) {
            return false;
        }

        return count($this -> tag -> getAttributes()) > 0;
    }


    /**
     * Returns whenever the node has a given attribute
     * @param string $attributeName
     * @return bool
     */
    public function hasAttribute(string $attributeName): bool {

        if(null === $this -> tag) {
            return false;
        }

        foreach($this -> tag -> getAttributes() as $attribute) {

            if($attribute -> getName() === $attributeName) {
                return true;
            }
        }

        return false;
    }


    /**
     * Removes a single attribute
     * @param string $attributeName
     * @return bool
     */
    public function removeAttribute(string $attributeName): bool {

        if(null === $this -> tag) {
            return false;
        }

        return $this -> tag -> removeAttribute($attributeName);
    }


    /**
     * Finds a given attribute and returns it
     * @param string $attributeName
     * @return null|Attribute
     */
    public function getAttribute(string $attributeName): ?Attribute {

        if(null === $this -> tag) {
            return null;
        }

        return $this -> tag -> getAttribute($attributeName);
    }


    /**
     * Finds a given attribute and returns it
     * @param string $key
     * @return null|Attribute
     */
    public function getAttributeKey(string $key): ?Attribute {

        if(null === $this -> tag) {
            return null;
        }

        foreach($this -> tag -> getAttributes() as $attribute) {

            if($key === $attribute -> getKey()) {
                return $attribute;
            }
        }

        return null;
    }


    /**
     * Returns the attribute list
     * @return array
     */
    public function getAttributes(): array {

        if(null === $this -> tag) {
            return [];
        }

        return $this -> tag -> getAttributes();
    }


    /**
     * Returns the output excluding the node tag as a string from the current node
     * @return string
     */
    public function getInnerContent(): string {

        $content = '';

        foreach($this -> getChildren() as $child) {

            if($child instanceof Node) {
                $content .= $child -> getTag() -> getContent();
            }

            $content .= $child -> getInnerContent();

            if($child instanceof Node && true === $child -> getTag() -> shouldHaveClosingTag()) {
                $content .= sprintf('</%s>', $child -> getTag() -> getName());
            }
        }

        return $content;
    }


    /**
     * Returns the output including the node tag itself as a string from the current node
     * @return string
     */
    public function getContent(): string {

        $content = $this -> getTag() -> getContent();
        $content .= $this -> getInnerContent();

        if($this -> getTag() -> shouldHaveClosingTag()) {
            $content .= sprintf('</%s>', $this -> getTag() -> getName());
        }

        return $content;
    }
}