<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Interfaces;

use sFire\Dom\Builder\Indexer;

interface IndexInterface extends AttributeInterface, TagInterface {

    public function getIndexer(): Indexer;

    public function setIndexer(Indexer $indexer): void;

    public function getObjectId(): int;
}