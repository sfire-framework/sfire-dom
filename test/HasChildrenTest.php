<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Dom\DomParser;
use sFire\Dom\DomNode;


final class HasChildrenTest extends TestCase {


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

        self::assertTrue($this -> domParser -> find('.foo') -> hasChildren());
        self::assertFalse($this -> domParser -> find('.non-existing-class') -> hasChildren());
        self::assertFalse($this -> domParser -> find('li') -> get() -> hasChildren());
    }
}