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


/**
 * Class DomElementAbstract
 * @package sFire\Dom
 */
Abstract class DomElementAbstract {


    /**w
     * Contains an instance of the next sibling of current node
     * @var null|DomElementAbstract
     */
    private ?DomElementAbstract $nextSibling = null;


    /**
     * Contains an instance of the previous sibling of current node
     * @var null|DomElementAbstract
     */
    private ?DomElementAbstract $previousSibling = null;


    /**
     * Contains the parent node of current node
     * @var null|DomElementAbstract
     */
    private ?DomElementAbstract $parent = null;


    /**
     * Sets the next sibling of current node element
     * @param DomElementAbstract $sibling
     * @return void
     */
    public function setNextSibling(DomElementAbstract $sibling): void {
        $this -> nextSibling = $sibling;
    }


    /**
     * Sets the previous sibling of current node element
     * @param DomElementAbstract $sibling
     * @return void
     */
    public function setPreviousSibling(DomElementAbstract $sibling): void {
        $this -> previousSibling = $sibling;
    }


    /**
     * Returns if the current node has a next sibling
     * @return bool
     */
    public function hasNextSibling(): bool {
        return null !== $this -> nextSibling;
    }


    /**
     * Returns the next sibling from the current node
     * @return null|DomElementAbstract
     */
    public function getNextSibling() {
        return $this -> nextSibling;
    }


    /**
     * Returns if the current node has a previous sibling
     * @return bool
     */
    public function hasPreviousSibling(): bool {
        return null !== $this -> previousSibling;
    }


    /**
     * Returns the previous sibling from the current node
     * @return null|DomElementAbstract
     */
    public function getPreviousSibling(): ?DomElementAbstract {
        return $this -> previousSibling;
    }


    /**
     * Set the parent node of current node
     * @param DomElementAbstract $parent
     * @return void
     */
    public function setParent(DomElementAbstract $parent): void {
        $this -> parent = $parent;
    }


    /**
     * Returns the parent node of current node
     * @return null|DomElementAbstract
     */
    public function getParent(): ?DomElementAbstract {
        return $this -> parent;
    }
}