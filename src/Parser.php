<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Dom;

use sFire\Dom\Elements\Comment;
use sFire\Dom\Elements\Node;
use sFire\Dom\Elements\Text;
use sFire\Dom\Exception\BadMethodCallException;
use sFire\Dom\Tags\Tag;


/**
 * Class Parser
 * @package sFire\Dom
 */
class Parser {


    /**
     * Contains the content type for HTML
     * @var int
     */
    public const CONTENT_TYPE_HTML = 1;


    /**
     * Contains the content type for XML
     * @var int
     */
    public const CONTENT_TYPE_XML = 2;


    /**
     * Contains the type of the content that needs to be parsed (html, xml)
     * @var null|int
     */
    private ?int $contentType = null;


    /**
     * Constructor
     * @param int $contentType
     * @throws BadMethodCallException
     */
    public function __construct(int $contentType = self::CONTENT_TYPE_HTML) {

        if(false === in_array($contentType, [self::CONTENT_TYPE_HTML, self::CONTENT_TYPE_XML])) {
            throw new BadMethodCallException(sprintf('Content type "%s" does not exists', $contentType));
        }

        $this -> contentType = $contentType;
    }


    /**
     * @param string $content
     * @return array
     */
    public function parse(string $content): array {

        $length  = strlen($content);
        $data    = null;
        $node    = null;
        $text    = ['content' => null, 'node' => null];
        $nodes   = [];
        $parents = [];

        for($i = 0; $i < $length; $i++) {

            $parent = end($parents);

            //Check if there is an opening/closing tag
            if($content[$i] === '<') {

                //Check if character is a HTML comment
                if('<!--' === substr($content, $i, 4)) {

                    $node = new Comment();

                    for($a = $i; $a < $length; $a++) {

                        $node -> appendContent($content[$a]);

                        if('-->' === substr($content, $a, 3)) {

                            $node -> appendContent(substr($content, ++$a, 2));
                            $i = $a + 1;
                            break;
                        }
                    }

                    continue;
                }

                //Next character should be an alphabetical character, otherwise it is not a valid tag name
                if(true === isset($content[$i + 1]) && true === (bool) preg_match('#[a-zA-Z/?]#', $content[$i + 1])) {

                    $tag  = $this -> getTag($content, $i);
                    $node = new Node($tag);

                    //Check if the tag is an opening tag i.e. <div>
                    if(true === $tag -> isOpeningTag()) {

                        if(null === $text['content']) {

                            if(true === $tag -> canHaveChildren()) {
                                $parents[] = $node;
                            }

                            //Check if the children of the node should be rendered as text
                            if(true === $tag -> shouldRenderChildrenAsText()) {

                                $text['content'] = new Text();
                                $text['content'] -> setParent($node);
                                $text['node']    = $node;

                                $node -> addChild($text['content']);

                                if(false !== $parent) {
                                    $parent -> addChild($node);
                                }
                                else {
                                    $nodes[] = $node;
                                }

                                continue;
                            }
                        }
                        else {

                            $text['content'] -> appendContent($node -> getTag() -> getContent());
                            continue;
                        }
                    }

                    //Tag is not an opening tag
                    else {

                        if(null !== $text['node']) {

                            if($text['node'] -> getTag() -> getName() === $tag -> getName()) {

                                $text['content'] = null;
                                $text['node'] = null;
                            }
                            else {

                                $text['content'] -> appendContent($tag -> getContent());
                                continue;
                            }
                        }

                        $closed = array_pop($parents);

                        if(null !== $closed) {

                            if($node -> getTag() -> getName() !== $closed -> getTag() -> getName()) {
                                $parents[] = $closed;
                            }
                        }

                        $node = null;
                        continue;
                    }
                }
            }

            if(null !== $text['content']) {

                $text['content'] -> appendContent($content[$i]);
                continue;
            }

            //Create a new text node
            if(null === $node) {
                $node = new Text();
            }

            //Set the next and previous node siblings
            $children = $parent ? $parent -> getChildren() : $nodes;
            $sibling  = end($children);

            if(false !== $sibling && $sibling !== $node) {

                $sibling -> setNextSibling($node);
                $node -> setPreviousSibling($sibling);
            }

            //If a parent exists
            if(false !== $parent) {

                //Add the child to the parent and mark the parent as the parent in the child node if the node exists in the parent and the node is plain text or is an opening tag
                if(false === $parent -> childExists($node) && ($node instanceof Text || $node instanceof Comment || true === $node -> getTag() -> isOpeningTag())) {

                    $parent -> addChild($node);
                    $node -> setParent($parent);
                }
            }

            //No parent exists
            else {

                //Check if the node is plain text or is an opening tag
                if($node instanceof Text || $node instanceof Comment || true === $node -> getTag() -> isOpeningTag()) {

                    if(false === in_array($node, $nodes, true)) {
                        $nodes[] = $node;
                    }
                }
            }

            //Append the content of the node if the node is plain text
            if($node instanceof Text) {
                $node -> appendContent($content[$i]);
            }
            else {
                $node = null;
            }
        }

        return $nodes;
    }


    /**
     * Finds the tag and returns it with the end position of the content where the tag was found
     * @param string $content
     * @param int $position
     * @return Tag
     */
    private function getTag(string $content, int &$position = 0): Tag {

        $tag              = '';
        $length           = strlen($content);
        $escapeCharacters = ['\'' => 0, '"' => 0];
        $escapeCharacter  = null;

        for($i = $position; $i < $length; $i++) {

            $tag .= $content[$i];

            if(true === isset($escapeCharacters[$content[$i]])) {

                if($content[$i] === $escapeCharacter) {
                    $escapeCharacter = null;
                }
                elseif(null === $escapeCharacter) {
                    $escapeCharacter = $content[$i];
                }
            }

            if('>' === $content[$i] && null === $escapeCharacter) {

                $position = $i;
                break;
            }
        }

        return new Tag($tag, $this -> contentType);
    }
}