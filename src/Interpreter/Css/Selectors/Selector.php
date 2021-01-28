<?php
declare(strict_types=1);

namespace sFire\Dom\Interpreter\Css\Selectors;


class Selector extends SelectorAbstract {

    private ?Id $id = null;
    private ?Pseudo $pseudo = null;
    private ?Tag $tag = null;


    private array $classNames = [];


    private array $attributes = [];


    /**
     * @var null|SelectorAbstract
     */
    private ?SelectorAbstract $current = null;


    public function getCurrent(): ?SelectorAbstract {
        return $this -> current;
    }



    /**
     * Appends the current content with a given single character
     * @param string $character A single character
     */
    public function appendContent(string $character): void {

        $this -> content ??= '';
        $this -> content .= $character;
    }



    public function append(string $character): void {

        if(null === $this -> current) {
            //Todo: throw exception
        }

        $this -> current -> append($character);
    }



    public function addTag(): void {

        $tag = new Tag();
        $this -> current ??= $tag;
        $this -> tag = $tag;
    }

    public function getTag(): ?Tag {
        return $this -> tag;
    }



    public function addClassName(): void {

        $this -> current = new ClassName();
        $this -> classNames[] = $this -> current;
    }


    /**
     * @return ClassName[]
     */
    public function getClassNames(): array {
        return $this -> classNames;
    }




    public function addId(): void {

        $id = new Id();
        $this -> current = $id;
        $this -> id = $id;
    }

    public function getId(): ?Id {
        return $this -> id;
    }




    public function addAttribute(): void {

        $attribute = new Attribute();
        $this -> current = $attribute;
        $this -> attributes[] = $attribute;
    }


    public function getAttributes(): array {
        return $this -> attributes;
    }


    public function addPseudo(): void {

        $pseudo = new Pseudo();
        $this -> current = $pseudo;
        $this -> pseudo = $pseudo;
    }


    public function getPseudo(): ?Pseudo {
        return $this -> pseudo;
    }
}