<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Traits;

trait Children {

    protected bool $renderChildrenAsText = false;
    protected bool $canHaveChildren = true;

    public function setRenderChildrenAsText(bool $renderChildrenAsText): void {
        $this -> renderChildrenAsText = $renderChildrenAsText;
    }

    public function shouldRenderChildrenAsText(): bool {
        return $this -> renderChildrenAsText;
    }

    public function setCanHaveChildren(bool $canHaveChildren): void {
        $this -> canHaveChildren = $canHaveChildren;
    }

    public function canHaveChildren(): bool {
        return $this -> canHaveChildren;
    }
}