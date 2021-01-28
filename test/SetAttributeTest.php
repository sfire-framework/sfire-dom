<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Dom\DomParser;
use sFire\Dom\DomNode;


final class SetAttributeTest extends TestCase {


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

        $this -> domParser -> find('div.foo') -> setAttribute('data-set');
        self::assertStringEqualsFile($this -> path . 'test-set-attribute-result-1.html', $this -> domParser -> render());
        self::assertEquals(1, $this -> domParser -> find('[data-set]') -> count());
    }


    /**
     * @return void
     */
    public function test2(): void {

        $this -> domParser -> find('div.foo') -> setAttribute('data-set', null, null);
        self::assertStringEqualsFile($this -> path . 'test-set-attribute-result-2.html', $this -> domParser -> render());
        self::assertEquals(1, $this -> domParser -> find('[data-set]') -> count());
    }


    /**
     * @return void
     */
    public function test3(): void {

        $this -> domParser -> find('div.foo') -> setAttribute('data-set', 'foo');
        self::assertStringEqualsFile($this -> path . 'test-set-attribute-result-3.html', $this -> domParser -> render());
        self::assertEquals(1, $this -> domParser -> find('[data-set="foo"]') -> count());
    }


    /**
     * @return void
     */
    public function test4(): void {

        $this -> domParser -> find('div.foo') -> setAttribute('data-set', 'foo', "'");
        self::assertStringEqualsFile($this -> path . 'test-set-attribute-result-4.html', $this -> domParser -> render());
        self::assertEquals(1, $this -> domParser -> find('[data-set="foo"]') -> count());
    }


    /**
     * @return void
     */
    public function test5(): void {

        $this -> domParser -> find('.foo') -> each(function(DomNode $node) {

            $node -> setAttribute('data-set', 'bar');
            self::assertTrue($node -> hasAttribute('data-set'));
        });

        self::assertStringEqualsFile($this -> path . 'test-set-attribute-result-5.html', $this -> domParser -> render());
        self::assertEquals(4, $this -> domParser -> find('[data-set]') -> count());
    }
}