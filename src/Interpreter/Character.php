<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter;


class Character {


    /**
     * Contains the data to be parsed
     * @var string|null
     */
    private string $data;


    /**
     * Contains the length of the data
     * @var int|null
     */
    private int $length;


    /**
     * Constructor
     * @param string $data The data to be parsed
     */
    public function __construct(string $data) {

        $this -> data = $data;
        $this -> length = strlen($data);
    }


    /**
     * Returns a single character at a given position
     * If position does not exists, it will return null
     * @param $position
     * @return string|null
     */
    public function get($position): ?string {
        return $this -> data[$position] ?? null;
    }


    /**
     * Returns the previous character based on a given position in the data
     * If previous character does not exists, it returns null
     * @param int $position The position of the data
     * @param false $skipWhitespace Enable or disable skipping whitespace as characters
     * @return string|null
     */
    public function previous(int $position, $skipWhitespace = false): ?string {

        if(false === $skipWhitespace) {
            return $this -> data[--$position] ?? null;
        }

        while($next = $this -> data[--$position] ?? false) {

            if(true === (bool) preg_match('/\s/', $next)) {
                continue;
            }

            return $next;
        }

        return null;
    }


    /**
     * Returns the next character based on a given position in the data
     * If next character does not exists, it returns null
     * @param int $position The position of the data
     * @param false $skipWhitespace Enable or disable skipping whitespace as characters
     * @return string|null
     */
    public function next(int $position, $skipWhitespace = false): ?string {

        if(false === $skipWhitespace) {
            return $this -> data[++$position] ?? null;
        }

        while($next = $this -> data[++$position] ?? false) {

            if(true === (bool) preg_match('/\s/', $next)) {
                continue;
            }

            return $next;
        }

        return null;
    }


    /**
     * Returns characters based on a range in the data
     * @param int $from The from position
     * @param int|null $to The to position
     * @return null|string
     */
    public function range(int $from, int $to = null): ?string {
        return substr($this->data, $from, $to - $from + 1);
    }


    /**
     * Returns the length of the data
     * @return int
     */
    public function length(): int {
        return $this -> length;
    }
}