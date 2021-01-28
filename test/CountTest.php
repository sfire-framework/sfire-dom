<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Dom\DomParser;
use sFire\Dom\DomNode;


final class CountTest extends TestCase {


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
        self::assertEquals(4, $this -> domParser -> find('.foo') -> count());
        self::assertEquals(0, $this -> domParser -> find('.non-existing-class') -> count());
    }
}