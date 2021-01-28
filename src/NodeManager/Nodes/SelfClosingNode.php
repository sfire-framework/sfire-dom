<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Nodes;

use sFire\Dom\NodeManager\Interfaces\AttributeInterface;
use sFire\Dom\NodeManager\Interfaces\IndexInterface;
use sFire\Dom\NodeManager\Interfaces\TagInterface;
use sFire\Dom\NodeManager\Traits\AttributeTrait;
use sFire\Dom\NodeManager\Traits\IndexTrait;
use sFire\Dom\NodeManager\Traits\NoChildrenTrait;
use sFire\Dom\NodeManager\Traits\TagTrait;


class SelfClosingNode extends NodeAbstract implements TagInterface, AttributeInterface, IndexInterface {


    use NoChildrenTrait;
    use TagTrait;
    use IndexTrait;
    use AttributeTrait;


    private CONST TYPE = 'void';


    /**
     * @inheritdoc
     */
    public function getType(): string {
        return self::TYPE;
    }


    /**
     * @inheritDoc
     * @return string
     */
    public function render(): string {

        $stack = [];
        $attributeStack = ['']; //Force a space between the tag name and attribute list

        foreach($this -> getAttributes() as $attribute) {
            $attributeStack[] = $attribute -> render();
        }

        $stack[] = sprintf('<%s%s />', $this -> getTagName(), implode(' ', $attributeStack));

        return implode('', $stack);
    }
}