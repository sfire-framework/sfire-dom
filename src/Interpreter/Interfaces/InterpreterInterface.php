<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Interfaces;

interface InterpreterInterface {


    /**
     * Returns all found items
     * @return array
     */
    public function getItems(): array;
}