<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Traits;

use sFire\Dom\Interpreter\Attribute\Attribute;
use sFire\Dom\NodeManager\Interfaces\IndexInterface;


trait AttributeTrait {


    /**
     * Contains the attributes as an array
     * @var Attribute[]
     */
    private array $attributes = [];


    /**
     * @inheritdoc
     */
    public function addAttribute(Attribute $attribute): void {

        $this -> attributes[spl_object_id($attribute)] = $attribute;

        if($this instanceof IndexInterface) {
            $this -> getIndexer() -> indexAttribute($this, $attribute);
        }
    }


    /**
     * @inheritdoc
     */
    public function setAttributes(array $attributes): void {

        foreach($attributes as $attribute) {
            $this -> addAttribute($attribute);
        }
    }


    /**
     * @inheritdoc
     */
    public function removeAttribute(string $attributeName): bool {

        foreach($this -> attributes as $id => $attribute) {

            if(null === $attribute -> getDirective() && $attributeName === $attribute -> getType()) {

                unset($this -> attributes[$id]);

                if($this instanceof IndexInterface) {
                    $this -> getIndexer() -> removeAttributeIndexByNode($this, $attribute);
                }

                return true;
            }
        }

        return false;
    }


    /**
     * @inheritdoc
     */
    public function getAttributes(): array {
        return $this -> attributes;
    }


    /**
     * @inheritdoc
     */
    public function hasAttribute(string $attributeName, ?string $directiveName = null): bool {

        foreach($this -> attributes as $id =>  $attribute) {

            if($attribute -> getType() === $attributeName) {

                if(null === $directiveName || $directiveName === $attribute -> getDirective()) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * @inheritdoc
     */
    public function getDirectives(string $directiveName): array {

        return array_filter($this -> attributes, static function(Attribute $attribute) use ($directiveName) {
            return $attribute -> getDirective() === $directiveName;
        });
    }


    /**
     * @inheritdoc
     */
    public function removeDirective(string $directiveName, ?string $attributeName = null): bool {

        foreach($this -> attributes as $id =>  $attribute) {

            if($attribute -> getDirective() === $directiveName) {

                if(null === $attributeName || $attributeName === $attribute -> getType()) {

                    unset($this -> attributes[$id]);

                    if($this instanceof IndexInterface) {
                        $this -> getIndexer() -> removeAttributeIndexByNode($this, $attribute);
                    }

                    return true;
                }
            }
        }

        return false;
    }


    /**
     * @inheritdoc
     */
    public function getAttribute(string $attributeName, ?string $directiveName = null): ?Attribute {

        foreach($this -> attributes as $id =>  $attribute) {

            if($attribute -> getType() === $attributeName) {

                if(null === $directiveName || $directiveName === $attribute -> getDirective()) {
                    return $attribute;
                }
            }
        }

        return null;
    }
}