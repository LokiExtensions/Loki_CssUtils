<?php declare(strict_types=1);

namespace Loki\CssUtils\Test\Integration\Util;

class CssClassTestInput
{
    public function __construct(
        public string $inputCssClass = 'input0',
        public array $targetBlockCssClasses = [],
        public array $globalBlockCssClasses = [],
        public array $groupBlockCssClasses = [],
        public array $legacyGroupBlockCssClasses = [],
        public string $expectedCssClass = ''
    ) {
    }
}
