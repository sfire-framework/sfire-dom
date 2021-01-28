<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Dom\DomParser;


/**
 * Class CssClass
 */
final class RemoveTest extends TestCase {


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

        $this -> domParser -> find('div.foo') -> remove();
        self::assertStringEqualsFile($this->path . 'test-remove-result-1.html', $this -> domParser -> render());
        self::assertEquals(0, $this -> domParser -> find('div.foo') -> count());
    }


    /**
     * @return void
     */
    public function test2(): void {

        $this -> domParser -> find('.foo') -> remove();
        self::assertStringEqualsFile($this->path . 'test-remove-result-2.html', $this -> domParser -> render());
        self::assertEquals(0, $this -> domParser -> find('.foo') -> count());
        self::assertEquals(0, $this -> domParser -> find('#biz') -> count());
    }
}