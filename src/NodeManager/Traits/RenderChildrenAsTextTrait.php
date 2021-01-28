<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Traits;

trait RenderChildrenAsTextTrait {

    use Children;

    protected bool $renderChildrenAsText = true;
}