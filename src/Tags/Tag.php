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

use sFire\Dom\Exception\RuntimeException;
use sFire\Dom\Parser;


/**
 * Class Tag
 * @package sFire\Dom
 */
class Tag {


    /**
     * Contains the tag name of the node
     * @var null|string
     */
    private ?string $name = null;


    /**
     * Contains the whole tag content including tag name
     * @var null|string
     */
    private ?string $content = null;


    /**
     * Contains the type of the content that needs to be parsed (html, xml)
     * @var int
     */
    private int $contentType = Parser::CONTENT_TYPE_HTML;


    /**
     * Contains the attributes of the node
     * @var Attribute[]
     */
    private array $attributes = [];


    /**
     * Contains if the tag is a closing tag (i.e. </div> or </item>)
     * @var bool
     */
    private bool $isClosingTag = false;


    /**
     * Contains if the tag is a self closing node (i.e. <br/> or <item />)
     * @var bool
     */
    private bool $isSelfClosingNode = false;


    /**
     * Contains if the tag is a language node (i.e. <?php or <?xml)
     * @var bool
     */
    private bool $isLanguageNode = false;


    /**
     * Contains if the tag can not contain a body/child node (i.e. <br> or <input>)
     * @var bool
     */
    private bool $isNoBodyNode = false;


    /**
     * Contains all the HTML nodes with no end tag
     * @var array
     */
    private array $noBodyNodes = ['!DOCTYPE', 'area', 'base', 'br', 'col', 'embed', 'frame', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track'];


    /**
     * Contains all HTML nodes that should render their children as normal text
     * @var array
     */
    private array $childrenTextTags = ['style', 'textarea', 'script'];


    /**
     * Contains if all child nodes should be rendered as plain text
     * @var bool
     */
    private bool $renderChildrenAsText = false;


    /**
     * Constructor
     * @param string $tag
     * @param int $contentType Defines the type of content (html, xml)
     * @throws RuntimeException
     */
    public function __construct(string $tag, int $contentType = Parser::CONTENT_TYPE_HTML) {

        $this -> content = $tag;
        $this -> contentType = $contentType;

        if(false === (bool) preg_match('#^<(?<close>/)?(?<languageTag>\?)?(?<name>[^\s/]+)(?<attributes>[\s\S]*?)(?<selfClose>/)?>$#', $tag, $match)) {
            throw new RuntimeException(sprintf('Tag "%s" is not a valid tag', $tag));
        }

        //Check if tag is a closing tag (i.e. </div> or </item>)
        $this -> isClosingTag = 1 === strlen($match['close']);

        //Check if tag is a self closing node (i.e. <br/> or <item />
        $this -> isSelfClosingNode = 1 === strlen($match['selfClose'] ?? '');

        //Check if tag is a self closing node (i.e. <br/> or <item />
        $this -> isLanguageNode = 1 === strlen($match['languageTag'] ?? '');

        //Check if node cannot contain a body (i.e. <br> or <input>)
        $this -> isNoBodyNode = true === in_array($match['name'], $this -> noBodyNodes) && $this -> contentType === Parser::CONTENT_TYPE_HTML;

        $this -> name = $match['name'];
        $this -> parseAttributes($match['attributes']);
    }


    /**
     * Returns the tag (i.e. <div> or <item>) with attributes (when provided)
     * @return null|string
     */
    public function getContent(): ?string {
        return $this -> content;
    }


    /**
     * Returns the tag name (i.e. div or item)
     * @return null|string
     */
    public function getName(): ?string {
        return $this -> name;
    }


    /**
     * Return all found attributes as an array
     * @return array
     */
    public function getAttributes(): array {
        return $this -> attributes;
    }


    /**
     * Return a single attribute if exists
     * @param string $attributeName
     * @return null|Attribute
     */
    public function getAttribute(string $attributeName): ?Attribute {

        foreach($this -> getAttributes() as $attribute) {

            if($attribute -> getName() === $attributeName) {
                return $attribute;
            }
        }

        return null;
    }


    /**
     * Return a single attribute if exists
     * @param string $attributeName
     * @return bool
     */
    public function removeAttribute(string $attributeName): bool {

        foreach($this -> attributes as $index => $attribute) {

            if($attribute -> getName() === $attributeName) {

                unset($this -> attributes[$index]);
                return true;
            }
        }

        return false;
    }


    /**
     * Returns whenever the node can not contain a body/child node (i.e. <br> or <input>)
     * @return bool
     */
    public function isNoBodyNode(): bool {
        return $this -> isNoBodyNode;
    }


    /**
     * Returns whenever the node is self closing (i.e. <br/> or <item />)
     * @return bool
     */
    public function isSelfClosingNode(): bool {
        return $this -> isSelfClosingNode;
    }


    /**
     * Returns whenever the tag is a closing tag (i.e. </div> or </item>)
     * @return bool
     */
    public function isClosingTag(): bool {
        return $this -> isClosingTag;
    }


    /**
     * Returns whenever the tag is a opening tag (i.e. <div> or <item>)
     * @return bool
     */
    public function isOpeningTag(): bool {
        return !$this -> isClosingTag;
    }


    /**
     * Returns whenever the tag is a language node (i.e. <?php or <?xml)
     * @return bool
     */
    public function isLanguageNode(): bool {
        return $this -> isLanguageNode;
    }


    /**
     * Returns if the nodes (children) inside the current node should be rendered as plain text
     * @return bool
     */
    public function shouldRenderChildrenAsText(): bool {
        return true === $this -> renderChildrenAsText || (true === in_array($this -> getName(), $this -> childrenTextTags) && $this -> contentType === Parser::CONTENT_TYPE_HTML);
    }


    /**
     * Returns if the tag should have a closing tag
     * @return bool
     */
    public function shouldHaveClosingTag(): bool {
        return false === $this -> isLanguageNode() && false === $this -> isClosingTag() && false === $this -> isNoBodyNode() && false === $this -> isSelfClosingNode();
    }


    /**
     * Sets all child nodes to be rendered as plain text
     * @param bool $boolean
     * @return void
     */
    public function setRenderChildrenAsText($boolean = true) :void {
        $this -> renderChildrenAsText = $boolean;
    }


    /**
     * Returns whenever the node can have children
     * @return bool
     */
    public function canHaveChildren(): bool {

        if(true === $this -> isClosingTag() || true === $this -> isSelfClosingNode() || true === $this -> isLanguageNode() || true === $this -> isNoBodyNode()) {
            return false;
        }

        return true;
    }


    /**
     * Parses the attribute lists and adds each found attribute to the local attribute list variable
     * @param string $content
     * @return void
     */
    private function parseAttributes(string $content): void {

        $content    = trim($content);
        $length     = strlen($content);
        $attribute  = new Attribute();

        for($i = 0; $i < $length; $i++) {

            if(true === (bool) preg_match('#[\s]#', $content[$i])) {

                if(false === (bool) preg_match('/^[\s]*=/', substr($content, $i), $match)) {

                    if(null !== $attribute -> getName()) {

                        $this -> attributes[] = $attribute;
                        $attribute = new Attribute();
                    }
                }

                continue;
            }
            elseif('=' === $content[$i]) {

                $enclosure = $this -> getAttributeEnclosure($content, $i);

                if(null !== $enclosure) {
                    $attribute -> setEnclosure($enclosure);
                }

                for($a = $i; $a < $length; $a++) {

                    if((null !== $enclosure && $enclosure === $content[$a]) || (null === $enclosure && $content[$a] === ' ')) {
                        break;
                    }

                    $attribute -> appendValue($content[$a]);
                }

                $i = $a;

                if(null !== $attribute -> getName()) {

                    $this -> attributes[] = $attribute;
                    $attribute  = new Attribute();
                }
            }
            else {
                $attribute -> appendName($content[$i]);
            }
        }

        if(null !== $attribute -> getName()) {
            $this -> attributes[] = $attribute;
        }
    }


    /**
     * Returns the attribute enclosure characters
     * @param string $content
     * @param int $position
     * @return string|null
     */
    private function getAttributeEnclosure(string $content, int &$position): ?string {

        $enclosure = null;
        $length = strlen($content);

        for($i = $position + 1; $i < $length; $i++) {

            if((bool) preg_match('/^[\s]/', $content[$i])) {
                continue;
            }

            if(true === in_array($content[$i], ['"', "'"])) {

                $enclosure = $content[$i];
                $i++;

                break;
            }

            if(false === (bool) preg_match('#[\s"\']#', $content[$i])) {
                break;
            }
        }

        $position = $i;
        return $enclosure;
    }
}