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
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringContainsString('&lt;/style&gt;', $html);
        $this->assertSame(1, substr_count($html, '</style>'));
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
