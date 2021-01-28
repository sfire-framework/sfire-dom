<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Interfaces;

use sFire\Dom\NodeManager\Nodes\NodeAbstract;


interface ParentInterface {


    public function appendChild(NodeAbstract $child): void;


    public function removeChild(NodeAbstract $targetChild): void;


    public function insertChildBefore(NodeAbstract $targetChild, NodeAbstract $beforeChild): void;


    public function insertChildAfter(NodeAbstract $targetChild, NodeAbstract $afterChild): void;


    public function prependChild(NodeAbstract $child): void;


    public function remove(): void;


    public function getFirstChild(array $excludeNodeTypes = []): ?NodeAbstract;


    public function getLastChild(array $excludeNodeTypes = []): ?NodeAbstract;


    public function findChildren(string $selector): array;


    public function getChildrenRecursive(callable $callback, NodeAbstract $targetNode = null): void;


    public function getChildrenByNodeType(array $nodeTypes): array;

    /**
     * @return NodeAbstract[]
     */
    public function getChildren(): array;
}