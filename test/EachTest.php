<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Dom\DomParser;
use sFire\Dom\DomNode;


final class EachTest extends TestCase {


    /**
     * Contains instance of DomParser
     * @var DomParser
     */
    private DomParser $domParser;


    /**
     * Setup
     * @return void
     */
    protected function setUp(): void {
        $this -> domParser = new DomParser(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'source.html'));
    }


    /**
     * @return void
     */
    public function test1(): void {

        $this -> domParser -> find('.foo') -> each(function($node, $index) {

            self::assertInstanceOf(DomNode::class, $node);
            self::assertIsInt($index);
        });
    }
}