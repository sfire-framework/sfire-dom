<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Traits;

use sFire\Dom\Builder\Indexer;


trait IndexTrait {


    /**
     * Contains an instance of Indexer
     * @var Indexer
     */
    private Indexer $indexer;


    /**
     * Sets the indexer for the current node
     * @param Indexer $indexer
     * @return void
     */
    public function setIndexer(Indexer $indexer): void {
        $this -> indexer = $indexer;
    }


    /**
     * Sets the indexer for the current node
     * @return Indexer
     */
    public function getIndexer(): Indexer {
        return $this -> indexer;
    }
}