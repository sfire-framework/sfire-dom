<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Dom\DomParser;


final class ClassTest extends TestCase {


    /**
     * Contains instance of DomParser
     * @var DomParser
     */
    private DomParser $domParser;


    /**
     * Setup. Created new DomParser instance
     * @return void
     */
    protected function setUp(): void {
        $this -> domParser = new DomParser(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'source.html'));
    }


    /**
     * @return void
     */
    public function testClassSelection(): void {

        self::assertEquals(4, $this -> domParser -> find('.foo') -> count());
        self::assertEquals(3, $this -> domParser -> find('li.foo') -> count());
        self::assertEquals(1, $this -> domParser -> find('div.foo') -> count());
        self::assertEquals(3, $this -> domParser -> find('.bar .foo') -> count());
        self::assertEquals(0, $this -> domParser -> find('.foo .bar') -> count());
        self::assertEquals(1, $this -> domParser -> find('.foo .foo') -> count());
        self::assertEquals(1, $this -> domParser -> find('.bar .foo .foo') -> count());
        self::assertEquals(0, $this -> domParser -> find('.foo .bar .foo') -> count());
    }
}