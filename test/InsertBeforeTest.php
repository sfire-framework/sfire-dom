<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Dom\DomParser;
use sFire\Dom\DomNode;


final class InsertBeforeTest extends TestCase {


    /**
     * Contains instance of DomParser
     * @var DomParser
     */
    private DomParser $domParser;


    /**
     * The path to the html source and result files
     * @var string|null
     */
    private ?string $path = null;


    /**
     * Setup
     * @return void
     */
    protected function setUp(): void {

        $this -> path = __DIR__ . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR;
        $this -> domParser = new DomParser(file_get_contents($this -> path . 'source.html'));
    }


    /**
     * @return void
     */
    public function test1(): void {

        $this -> domParser -> find('div.foo') -> insertBefore('<a><h1 class="luez">test</h1></a>');
        self::assertStringEqualsFile($this->path . 'test-insert-before-result-1.html', $this -> domParser -> render());
        self::assertEquals(1, $this -> domParser -> find('.luez') -> count());
        self::assertEquals(1, $this -> domParser -> find('div.foo') -> count());
    }


    /**
     * @return void
     */
    public function test2(): void {

        $nodes = $this -> domParser -> find('#biz') -> copy();
        $this -> domParser -> find('div.foo') -> insertBefore($nodes);
        self::assertStringEqualsFile($this->path . 'test-insert-before-result-2.html', $this -> domParser -> render());
        self::assertEquals(2, $this -> domParser -> find('#biz') -> count());
    }


    /**
     * @return void
     */
    public function test3(): void {

        $this -> domParser -> find('#biz') -> each(function(DomNode $node) {
            $this -> domParser -> find('div.foo') -> insertBefore($node);
        });

        self::assertStringEqualsFile($this->path . 'test-insert-before-result-3.html', $this -> domParser -> render());
        self::assertEquals(2, $this -> domParser -> find('#biz') -> count());
    }
}