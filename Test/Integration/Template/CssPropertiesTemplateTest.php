<?php declare(strict_types=1);

namespace Loki\CssUtils\Test\Integration\Template;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class CssPropertiesTemplateTest extends TestCase
{
    public function testRendersCssProperties(): void
    {
        $html = $this->render([
            'foo' => 'bar',
            'loki-spacing' => '4px',
        ]);

        $this->assertStringContainsString('<style>', $html);
        $this->assertStringContainsString(':root {', $html);
        $this->assertMatchesRegularExpression('/--foo: bar;/', $html);
        $this->assertMatchesRegularExpression('/--loki-spacing: 4px;/', $html);
    }

    public function testRendersEmptyRootWithoutProperties(): void
    {
        $html = $this->render(null);

        $this->assertStringContainsString(':root {', $html);
        $this->assertStringNotContainsString('--', $html);
    }

    public function testEscapesCssProperties(): void
    {
        $html = $this->render(['foo<b>' => '</style><script>alert(1)</script>']);

        $this->assertStringNotContainsString('</style><script>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertSame(1, substr_count($html, '</style>'));
        $this->assertSame(1, substr_count($html, '<style>'));
    }

    public function testRendersQuotesAndAmpersandsAsValidCss(): void
    {
        $html = $this->render([
            'loki-font' => '"Open Sans", sans-serif',
            'loki-image' => "url('/media/a&b.png')",
        ]);

        $this->assertStringContainsString('--loki-font: "Open Sans", sans-serif;', $html);
        $this->assertStringContainsString("--loki-image: url('/media/a&b.png');", $html);
    }

    public function testSanitizesCssPropertyNames(): void
    {
        $html = $this->render(['foo; color: red' => '1px']);

        $this->assertStringContainsString('--foocolorred: 1px;', $html);
    }

    public function testSkipsInvalidCssProperties(): void
    {
        $html = $this->render([
            'array' => ['foo'],
            'null' => null,
            '' => 'empty-name',
            'number' => 42,
        ]);

        $this->assertStringNotContainsString('--array', $html);
        $this->assertStringNotContainsString('--null', $html);
        $this->assertStringNotContainsString('empty-name', $html);
        $this->assertStringContainsString('--number: 42;', $html);
    }

    private function render(?array $cssProperties): string
    {
        $layout = ObjectManager::getInstance()->create(LayoutInterface::class);
        $block = $layout->createBlock(Template::class, 'loki.css-properties', [
            'data' => [
                'template' => 'Loki_CssUtils::stylesheet/css-properties.phtml',
                'css_properties' => $cssProperties,
            ],
        ]);

        return $block->toHtml();
    }
}
