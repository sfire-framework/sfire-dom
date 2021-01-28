<?php
declare(strict_types=1);

namespace sFire\Dom\Builder;

use sFire\Dom\Interpreter\Interfaces\InterpreterInterface;
use sFire\Dom\NodeManager\Nodes\NodeAbstract;


interface BuilderInterface {


    /**
     * Constructor
     * @param InterpreterInterface $interpreter
     */
    public function __construct(InterpreterInterface $interpreter);


    /**
     * Returns the parsed dom as a tree with children and parent nodes
     * @return NodeAbstract[]
     */
    public function getDomTree(): array;
}