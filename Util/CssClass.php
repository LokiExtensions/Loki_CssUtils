<?php declare(strict_types=1);

namespace Loki\CssUtils\Util;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Loki\CssUtils\Util\CssClassParser\CssClassParserInterface;

class CssClass
{
    private ?Template $block = null;

    /**
     * @param CssClassParserInterface[] $cssClassParsers
     */
    public function __construct(
        private readonly array $cssClassParsers = [],
    ) {
    }

    public function setBlock(Template $block): CssClass
    {
        $this->block = $block;

        return $this;
    }

    public function __invoke(string $defaultCss = '', string $scope = 'block'): string
    {
        $cssClasses = $this->getDefaultCssClasses();
        if (!isset($cssClasses[$scope])) {
            $cssClasses[$scope] = [];
        }

        $defaultCss = trim($defaultCss);
        if ($defaultCss !== '' && $defaultCss !== '0') {
            $cssClasses[$scope]['default'] = $defaultCss;
        }

        $blockCssClasses = (array)$this->block->getData('css_classes');
        $cssClasses = array_merge_recursive($cssClasses, $blockCssClasses);

        // @todo: Beautify this
        $nameInLayout = $this->block->getNameInLayout();

        $defaultsBlock = $this->block->getLayout()->getBlock('loki-components.css_classes');
        if ($defaultsBlock instanceof AbstractBlock) {
            $blockData = $defaultsBlock->getData($nameInLayout);
            if (!empty($blockData)) {
                $cssClasses = array_merge_recursive($cssClasses, $blockData);
            }
        }

        $cssClasses[$scope] = $this->parse($cssClasses[$scope], $scope);

        $css = '';
        foreach ($cssClasses[$scope] as $cssClassValue) {
            if (!is_string($cssClassValue)) {
                $cssClassValue = array_pop($cssClassValue);
            }

            $css .= ' ' . $cssClassValue;
        }

        $cssName = $this->block->getCssName();
        if (empty($cssName)) {
            $cssName = preg_replace('/([^0-9a-zA-Z]+)/', '-', (string)$nameInLayout);
        }

        $css = 'scope-' . $scope . ' ' . trim($css);
        $scopeClass = $scope === 'block' ? $cssName : $cssName . '__' . $scope;

        $css = $scopeClass . ' ' . trim($css);

        return trim($css);
    }

    private function getDefaultCssClasses(): array
    {
        $templateId = preg_replace('/^(.*)::/', '', (string)$this->block->getTemplate());
        $templateId = preg_replace('/\.phtml/', '', $templateId);
        $templateId = str_replace('/', '.', $templateId);

        $defaultBlockName = 'loki-components.defaults.' . $templateId;
        $defaultBlock = $this->block->getLayout()->getBlock($defaultBlockName);
        if (false === $defaultBlock instanceof AbstractBlock) {
            return [];
        }

        return (array)$defaultBlock->getData('css_classes');
    }

    private function parse(array $cssClasses, string $scope): array
    {
        foreach ($this->cssClassParsers as $cssClassParser) {
            if (false === $cssClassParser instanceof CssClassParserInterface) {
                continue;
            }

            $cssClasses = $cssClassParser->parse($cssClasses, $scope, $this->block);
        }

        return $cssClasses;
    }
}
