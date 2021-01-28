<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Interfaces;

use \sFire\Dom\Interpreter\Attribute\Attribute;


interface AttributeInterface {


    /**
     * Adds a new attribute and indexes it
     * @param Attribute $attribute
     * @return void
     */
    public function addAttribute(Attribute $attribute): void;


    /**
     * Adds an array of Attribute instances and indexes all the attributes
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void;


    /**
     * Removes an attribute by a provided attribute name
     * @param string $attributeName
     * @return bool Returns true if attribute is successfully removed
     */
    public function removeAttribute(string $attributeName): bool;


    /**
     * Returns all attributes as an array of Attributes instances
     * @return Attribute[]
     */
    public function getAttributes(): array;


    /**
     * Returns if the node has a attribute with a provided name. If the second parameter $directiveName is provided, it will return the true if the attribute also contains the provided directive name
     * @param string $attributeName The name of the attribute
     * @param string|null $directiveName
     * @return bool
     */
    public function hasAttribute(string $attributeName, ?string $directiveName = null): bool;


    /**
     * Returns all instances of attributes by a provided Directive
     * @param string $directiveName The name of the directive (i.e. bind:id="foo", bind:class="bar" where bind is the directive)
     * @return array
     */
    public function getDirectives(string $directiveName): array;


    /**
     * Removes a directive based on directive name. If second parameter $attributeName is provided, only the attribute name which belongs to the directive will be removed
     * @param string $directiveName The name of the directive (i.e. bind:id="foo", bind:class="bar" where bind is the directive)
     * @param null|string $attributeName The name of the attribute which belongs to the provided directive name
     * @return bool Returns true if attribute is successfully removed
     */
    public function removeDirective(string $directiveName, ?string $attributeName = null): bool;


    /**
     * Returns a single Attribute instance based on a provided attribute name. If the second parameter $directiveName is provided, it will return the Attribute which contains also the provided directive name
     * @param string $attributeName
     * @param string|null $directiveName
     * @return null|Attribute
     */
    public function getAttribute(string $attributeName, ?string $directiveName = null): ?Attribute;
}