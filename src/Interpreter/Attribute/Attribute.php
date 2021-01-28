<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Attribute;


class Attribute {


    public const STATE_DIRECTIVE = 'directive';
    public const STATE_TYPE = 'type';
    public const STATE_VALUE = 'value';


    /**
     * Contains the attribute directive part (i.e. v-bind:class="", where v-bind is the directive)
     * @var null|string
     */
    private ?string $directive = null;


    /**
     * Contains the attribute type part (i.e. class="" or v-bind:class="", where class is the type)
     * @var null|string
     */
    private ?string $type = null;


    /**
     * Contains the attribute value part (i.e. class="foo" where foo is the value)
     * @var null|string
     */
    private ?string $value = null;


    /**
     * Contains the attribute enclosure type (i.e. class="foo" where " is the enclosure type)
     * @var null|string
     */
    private ?string $enclosureType = null;


    private ?string $state = null;


    public function setState(string $state): void {
        $this -> state = $state;
    }


    public function getState(): ?string {
        return $this -> state;
    }


    /**
     * Appends the given character to the type part of the attribute
     * @param string $character
     * @return void
     */
    public function appendType(string $character): void {

        $this -> type ??= '';
        $this -> type .= $character;
    }


    /**
     * Returns the attribute type part (i.e. class="" or v-bind:class="", where class is the type)
     * @return null|string
     */
    public function getType(): ?string {
        return $this -> type;
    }


    /**
     * Sets (and overwrites) the type part of the attribute (i.e. class="" or v-bind:class="", where class is the type)
     * @param null|string $type
     * @return self
     */
    public function setType(?string $type): self {

        $this -> state = self::STATE_TYPE;
        $this -> type = $type;
        return $this;
    }


    public function append(string $character): void {

        if(self::STATE_VALUE === $this -> state || (null !== $this -> getEnclosureType() && null === $this -> getType())) {

            $this -> appendValue($character);
            return;
        }

        $this -> appendType($character);
    }


    /**
     * Appends the given character to the directive part of the attribute
     * @param string $character
     * @return void
     */
    public function appendDirective(string $character): void {

        $this -> directive ??= '';
        $this -> directive .= $character;
    }


    /**
     * Sets (and overwrites) the directive part of the attribute (i.e. v-bind:class="", where v-bind is the directive)
     * @param null|string $directive
     * @return self
     */
    public function setDirective(?string $directive): self {

        $this -> directive = $directive;
        return $this;
    }


    /**
     * Returns the directive part of the attribute (i.e. v-bind:class="", where v-bind is the directive)
     * @return null|string
     */
    public function getDirective(): ?string {
        return $this -> directive;
    }


    /**
     * Sets the enclosure type (i.e. " or ' or null)
     * @param null|string $enclosureType
     * @return self
     */
    public function setEnclosureType(?string $enclosureType) : self {

        $this -> enclosureType = $enclosureType;
        return $this;
    }


    /**
     * Returns the enclosure type (i.e. " or ' or null)
     * @return null|string
     */
    public function getEnclosureType(): ?string {
        return $this -> enclosureType;
    }


    /**
     * Appends the value part of the attribute (i.e. class="foo" where foo is the value)
     * @param string $character
     * @return void
     */
    public function appendValue(string $character): void {

        $this -> state = self::STATE_VALUE;
        $this -> value ??= '';
        $this -> value .= $character;
    }


    /**
     * Sets the value part of the attribute (i.e. class="foo" where foo is the value)
     * @param string|null $value
     * @return self
     */
    public function setValue(?string $value): self {

        $this -> value = $value;
        return $this;
    }


    /**
     * Returns the value part of the attribute (i.e. class="foo" where foo is the value)
     * @return null|string
     */
    public function getValue(): ?string {
        return $this -> value;
    }


    /**
     * Returns the parsed/rendered attribute as a whole string
     * @return null|string
     */
    public function render(): ?string {

        $attribute = '';

        if(null !== $this -> directive) {
            $attribute = $this -> directive . ':';
        }

        if(null !== $this -> type) {
            $attribute .= $this -> type;
        }

        if(null !== $this -> value || null !== $this -> enclosureType) {
            $attribute .= sprintf('=%s%s%1$s', $this -> enclosureType, $this -> value);
        }

        return $attribute;
    }
}