<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter;

use sFire\Dom\Interpreter\Interfaces\InterpreterInterface;

abstract class InterpreterAbstract implements InterpreterInterface {


    protected const NEW_LINE = "\n";
    protected const TAB = "\t";
    protected const CARRIAGE = "\r";
    protected const SPACE = ' ';
    protected const NUL_BYTE = "\0";
    protected const VERTICAL_TAB = "\x0B";
    protected const WHITESPACE_CHARACTERS = [

        self::SPACE => 1,
        self::VERTICAL_TAB => 1,
        self::TAB => 1,
        self::NUL_BYTE => 1,
        self::CARRIAGE => 1,
        self::NEW_LINE => 1
    ];


    /**
     * Returns all found items
     * @return array
     */
    abstract public function getItems(): array;



    /**
     * Returns if a given character is a whitespace character like \r \t \n or space
     * @param null|string $character The character to check
     * @return bool
     */
    protected function isWhiteSpace(?string $character): bool {
        return true === isset(self::WHITESPACE_CHARACTERS[$character]);
    }


    /**
     * Returns if a given character is an alpha character (between a-z and A-Z)
     * @param string $character The character to check
     * @return bool
     */
    protected function isAlpha(string $character): bool {
        return ($character >= 'a' && $character <= 'z') || ($character >= 'A' && $character <= 'Z');
    }


    /**
     * Returns if a given character is an alpha numeric character (between a-z, A-Z and 0-9)
     * @param string $character The character to check
     * @return bool
     */
    protected function isAlphaNumeric(string $character): bool {

        if(true === $this -> isAlpha($character)) {
            return true;
        }

        return true === is_numeric($character);
    }


    /**
     * Returns if a given character is an alpha numeric dashed underscore character (between a-z, A-Z and 0-9 and the - and _ character)
     * @param string $character The character to check
     * @return bool
     */
    protected function isAlphaNumericDashedUnderscored(string $character): bool {

        if(true === $this -> isAlphaNumeric($character)) {
            return true;
        }

        return true === in_array($character, ['-', '_'], true);
    }
}