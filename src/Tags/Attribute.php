<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Dom\Tags;


/**
 * Class Attribute
 * @package sFire\Dom
 */
class Attribute {


    /**
     * Contains the name of the attribute
     * @var null|string
     */
    private ?string $name = null;


    /**
     * Contains the enclosure type of the attribute (i.e. ' or " character)
     * @var null|string
     */
    private ?string $enclosure = null;


    /**
     * Contains the value of the attribute
     * @var null|string
     */
    private ?string $value = null;


    /**
     * Contains the parsed html attribute
     * @var null|string
     */
    private ?string $parsed = null;


    /**
     * Set the name of the attribute (i.e. class or title)
     * @param string $name The name of the attribute
     * @return void
     */
    public function setName(string $name): void {
        $this -> name = trim($name);
    }


    /**
     * Returns the key of the name of the attribute (i.e. bind:href="" will return "href")
     * @return null|string
     */
    public function getKey(): ?string {

        $name = $this -> getName();

        if(null === $name) {
            return null;
        }

        return explode(':', $name)[0] ?? null;
    }


    /**
     * Returns the type of the name of the attribute (i.e. bind:href="" will return "bind")
     * @return null|string
     */
    public function getType(): ?string {

        $name = $this -> getName();

        if(null === $name) {
            return null;
        }

        return explode(':', $name)[1] ?? null;
    }


    /**
     * Returns the whole attribute containing the attribute name, enclosure and value
     * @return string|null
     */
    public function getParsed(): ?string {

        if(null === $this -> parsed) {
            $this -> parse();
        }

        return $this -> parsed;
    }


    /**
     * Sets the whole attribute containing the attribute name, enclosure and value
     * @param string $attribute
     * @return void
     */
    public function setParsed(string $attribute): void {
        $this -> parsed = $attribute;
    }


    /**
     * Returns the name of the attribute
     * @return null|string
     */
    public function getName(): ?string {
        return $this -> name;
    }


    /**
     * Append the name of the attribute
     * @param string $name
     * @return void
     */
    public function appendName(string $name): void {

        if(null === $this -> name) {
            $this -> name = '';
        }

        $this -> name .= $name;
        $this -> name = trim($this -> name);

        if(0 === strlen($this -> name)) {
            $this -> name = null;
        }
    }


    /**
     * Set the enclosure type of the attribute (i.e. ' or " character)
     * @param string $enclosure The enclosure type of the attribute
     * @return void
     */
    public function setEnclosure(string $enclosure): void {
        $this -> enclosure = $enclosure;
    }


    /**
     * Returns the enclosure of the attribute (i.e. ' or " character)
     * @return null|string
     */
    public function getEnclosure(): ?string {
        return $this -> enclosure;
    }


    /**
     * Set the value of the attribute
     * @param string $value The value of the attribute
     * @return void
     */
    public function setValue(string $value): void {
        $this -> value = $value;
    }


    /**
     * Append the value of the attribute
     * @param string $value
     * @return void
     */
    public function appendValue(string $value): void {

        if(null === $this -> value) {
            $this -> value = '';
        }

        $this -> value .= $value;
    }

    /**
     * Returns the value of the attribute
     * @return null|string
     */
    public function getValue(): ?string {
        return $this -> value;
    }


    /**
     * Converts the attribute name, enclosure and value to a single data string
     * @return void
     */
    private function parse(): void {

        $parsed = sprintf('%s%s%s%s%3$s', $this -> getName(), null !== $this -> getEnclosure() || null !== $this -> getValue() ? '=' : null, $this -> getEnclosure(), $this -> getValue());
        $this -> parsed = strlen($parsed) > 0 ? $parsed : null;
    }
}