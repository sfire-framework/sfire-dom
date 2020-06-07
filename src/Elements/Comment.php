<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Dom\Elements;


/**
 * Class Comment
 * @package sFire\Dom
 */
class Comment extends DomElementAbstract {


    /**
     * Contains the content
     * @var string
     */
    private string $content = '';


    /**
     * Appends the current content with new content
     * @param string $content
     */
    public function appendContent(string $content) {
        $this -> content .= $content;
    }


    /**
     * Returns the content
     * @return string
     */
    public function getContent() {
        return $this -> content;
    }
}