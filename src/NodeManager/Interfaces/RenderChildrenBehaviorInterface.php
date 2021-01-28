<?php
declare(strict_types=1);

namespace sFire\Dom\NodeManager\Interfaces;

interface RenderChildrenBehaviorInterface {

    public function shouldRenderChildrenAsText(): bool;
}