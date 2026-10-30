<?php declare(strict_types=1);

namespace Loki\CssUtils\Util;

use Magento\Framework\View\Element\Template;
use Loki\CssUtils\Util\CssStyleParser\CssStyleParserInterface;

class CssStyle
{
    private ?Template $block = null;

    /**
     * @param CssStyleParserInterface[] $cssStyleParsers
     */
    public function __construct(
        private readonly array $cssStyleParsers = [],
    ) {
    }

    public function setBlock(Template $block): CssStyle
    {
        $this->block = $block;

        return $this;
    }

    public function __invoke(string $defaultCss = '', string $scope = 'block'): string
    {
        $cssStyles = array_merge(
            $this->getDefaultCssStyles($defaultCss),
            $this->getCssStyles($scope)
        );

        $cssStyles = $this->parse($cssStyles);

        $css = '';
        foreach ($cssStyles as $cssStyleName => $cssStyleValue) {
            if (!is_string($cssStyleValue) && !is_int($cssStyleValue) && !is_float($cssStyleValue)) {
                continue;
            }

            $css .= $cssStyleName . ':' . $cssStyleValue . ';';
        }

        return trim($css, " \f\n\r\t\v\x00");
    }

    /**
     * Flat values in `css_styles` apply to the `block` scope, array values define styles per scope
     */
    private function getCssStyles(string $scope): array
    {
        $flatCssStyles = [];
        $scopedCssStyles = [];

        foreach ((array)$this->block->getData('css_styles') as $name => $value) {
            if (is_array($value)) {
                if ((string)$name === $scope) {
                    $scopedCssStyles = $value;
                }

                continue;
            }

            if ($scope === 'block') {
                $flatCssStyles[$name] = $value;
            }
        }

        return array_merge($flatCssStyles, $scopedCssStyles);
    }

    private function getDefaultCssStyles(string $defaultCss): array
    {
        $cssStyles = [];
        $declarations = preg_split('/;(?![^(]*\))/', $defaultCss) ?: [];
        foreach ($declarations as $declaration) {
            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0], " \f\n\r\t\v\x00");
            $value = trim($parts[1], " \f\n\r\t\v\x00");
            if ($name === '' || $value === '') {
                continue;
            }

            $cssStyles[$name] = $value;
        }

        return $cssStyles;
    }

    private function parse(array $cssStyles): array
    {
        foreach ($this->cssStyleParsers as $cssStyleParser) {
            if (false === $cssStyleParser instanceof CssStyleParserInterface) {
                continue;
            }

            $cssStyles = $cssStyleParser->parse($cssStyles, $this->block);
        }

        return $cssStyles;
    }
}
