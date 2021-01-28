<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Css;


use sFire\Dom\Interpreter\Css\Selectors\SelectorAbstract;

class SelectorGroup {


    /**
     * Contains all items
     * @var SelectorAbstract[]
     */
    private array $items = [];


    /**
     * Returns all found instances of SelectorGroup
     * @param bool $reversedOrder
     * @return SelectorAbstract[]
     */
    public function getItems(bool $reversedOrder = false): array {
        return true === $reversedOrder ? array_reverse($this -> items) : $this -> items;
    }


    /**
     * Add a single instance of SelectorGroup to the items list
     * @param SelectorAbstract $item
     * @return void
     */
    public function addItem(SelectorAbstract $item): void {
        $this -> items[] = $item;
    }
}