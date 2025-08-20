<?php
declare(strict_types=1);

namespace Loki\CssUtils\Util\CssStyleParser;

use Magento\Framework\View\Element\AbstractBlock;

interface CssStyleParserInterface
{
    public function parse(array $cssStyles, AbstractBlock $block): array;
}
