<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Nodes;


class OpeningNode extends ParentNodeAbstract {


    private CONST TYPE = 'node';


    /**
     * @inheritdoc
     */
    public function getType(): string {
        return self::TYPE;
    }



    /**
     * @inheritdoc
     * @return string
     */
    public function render(): string {

        $stack = [];
        $attributeStack = ['']; //Force a space between the tag name and attribute list

        foreach($this -> getAttributes() as $attribute) {
            $attributeStack[] = $attribute -> render();
        }

        //$attributeStack[] = $this -> getObjectId();

        $stack[] = sprintf('<%s%s>', $this -> getTagName(), implode(' ', $attributeStack));

        foreach($this -> children as $child) {
            $stack[] = $child -> render();
        }

        if(true === $this -> canHaveChildren()) {
            $stack[] = sprintf('</%s>', $this -> getTagName());
        }

        return implode('', $stack);
    }
}