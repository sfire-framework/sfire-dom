<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Dom\DomParser;
use sFire\Dom\DomNode;


final class GetTest extends TestCase {


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

        $this -> domParser -> find('.foo') -> get() -> setAttribute('class', 'bar');
        self::assertStringEqualsFile($this -> path . 'test-get-result-1.html', $this -> domParser -> render());
    }


    /**
     * @return void
     */
    public function test2(): void {

        $this -> domParser -> find('.foo') -> get(2) -> setAttribute('class', 'bar');
        self::assertStringEqualsFile($this -> path . 'test-get-result-2.html', $this -> domParser -> render());
    }


    /**
     * @return void
     */
    public function test3(): void {
        self::assertNull($this -> domParser -> find('.foo') -> get(200));
    }
}