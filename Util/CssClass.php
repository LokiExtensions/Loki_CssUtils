<?php declare(strict_types=1);

namespace Loki\CssUtils\Util;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Loki\CssUtils\Util\CssClassParser\CssClassParserInterface;
use Magento\Framework\View\LayoutInterface;

class CssClass
{
    private ?Template $block = null;

    /**
     * @param CssClassParserInterface[] $cssClassParsers
     */
    public function __construct(
        private LayoutInterface $layout,
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
        $cssClasses[$scope]['default'] = $defaultCss;

        $cssClasses = array_merge_recursive(
            $cssClasses,
            $this->getPerBlockCssClasses(),
            $this->getGlobalCssClasses(),
            $this->getCssClassesFromLegacyBlockGroup(),
            $this->getCssClassesFromBlockGroup(),
        );

        $cssClasses[$scope] = $this->parse($cssClasses[$scope], $scope);

        $css = '';
        foreach ($cssClasses[$scope] as $cssClassValue) {
            if (!is_string($cssClassValue)) {
                $cssClassValue = array_pop($cssClassValue);
            }

            $css .= ' ' . $cssClassValue;
        }

        $cssName = $this->block->getCssName();
        $nameInLayout = $this->block->getNameInLayout();
        if (empty($cssName)) {
            $cssName = strtolower(preg_replace('/([^0-9a-zA-Z]+)/', '-', (string)$nameInLayout));
        }

        $css = 'scope-' . $scope . ' ' . trim($css);
        $scopeClass = $scope === 'block' ? $cssName : $cssName . '__' . $scope;

        $css = $scopeClass . ' ' . trim($css);

        return trim($css);
    }

    private function getPerBlockCssClasses(): array
    {
        return (array)$this->block->getData('css_classes');
    }

    private function getGlobalCssClasses(): array
    {
        $nameInLayout = $this->block->getNameInLayout();
        $globalBlock = $this->layout->getBlock('loki-components.css_classes');
        if ($globalBlock instanceof AbstractBlock) {
            return (array)$globalBlock->getData($nameInLayout);
        }

        return [];
    }

    private function getCssClassesFromBlockGroup(): array
    {
        $cssClasses = [];

        $blockGroupIds = $this->getCssClassGroups();
        foreach ($blockGroupIds as $blockGroupId) {
            $block = $this->layout->getBlock('loki-components.css_classes.' . $blockGroupId);
            if (false === $block instanceof AbstractBlock) {
                continue;
            }

            foreach ((array) $block->getData() as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $cssClasses[$key] = $value;
            }
        }

        return $cssClasses;
    }

    /**
     * @deprecated The block `loki-components.defaults.XYZ` will be renamed to `loki-components.css_classes.group.XYZ` with the next major release.
     */
    private function getCssClassesFromLegacyBlockGroup(): array
    {
        $cssClasses = [];

        $blockGroupIds = $this->getCssClassGroups();
        foreach ($blockGroupIds as $blockGroupId) {
            $block = $this->layout->getBlock('loki-components.defaults.' . $blockGroupId);
            if (false === $block instanceof AbstractBlock) {
                continue;
            }

            foreach ((array) $block->getData('css_classes') as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                $cssClasses[$key] = $value;
            }
        }

        return $cssClasses;
    }

    private function getCssClassGroups(): array
    {
        $cssClassGroups = (array)$this->block->getCssClassGroups();
        $cssClassGroups[] = $this->getTemplateId();
        return $cssClassGroups;
    }

    private function getTemplateId(): string
    {
        $templateId = preg_replace('/^(.*)::/', '', (string)$this->block->getTemplate());
        $templateId = preg_replace('/\.phtml/', '', $templateId);
        return str_replace('/', '.', $templateId);
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
