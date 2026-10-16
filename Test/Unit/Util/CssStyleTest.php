<?php declare(strict_types=1);

namespace Loki\CssUtils\Test\Unit\Util;

use Loki\CssUtils\Util\CssStyle;
use Loki\CssUtils\Util\CssStyleParser\CssStyleParserInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use PHPUnit\Framework\TestCase;
use stdClass;

class CssStyleTest extends TestCase
{
    public function testWithoutCssStyles(): void
    {
        $cssStyle = (new CssStyle())->setBlock($this->getBlock(null));
        $this->assertSame('', $cssStyle());
    }

    public function testWithCssStyles(): void
    {
        $cssStyle = (new CssStyle())->setBlock($this->getBlock(['color' => 'red', 'margin' => '0']));
        $this->assertSame('color:red;margin:0;', $cssStyle());
    }

    public function testDefaultCssIsUsed(): void
    {
        $cssStyle = (new CssStyle())->setBlock($this->getBlock(null));
        $this->assertSame('padding:1px;margin:0;', $cssStyle('padding: 1px; margin:0'));
    }

    public function testDefaultCssIsExtendedAndOverridden(): void
    {
        $cssStyle = (new CssStyle())->setBlock($this->getBlock(['color' => 'red', 'padding' => '2px']));
        $this->assertSame('padding:2px;margin:0;color:red;', $cssStyle('padding:1px;margin:0;'));
    }

    public function testDefaultCssWithSemicolonInsideParentheses(): void
    {
        $cssStyle = (new CssStyle())->setBlock($this->getBlock(null));
        $this->assertSame(
            'background:url(data:image/png;base64,AAAA);color:red;',
            $cssStyle('background:url(data:image/png;base64,AAAA);color:red')
        );
    }

    public function testFlatCssStylesOnlyApplyToBlockScope(): void
    {
        $cssStyle = (new CssStyle())->setBlock($this->getBlock(['color' => 'red']));
        $this->assertSame('color:red;', $cssStyle());
        $this->assertSame('padding:1px;', $cssStyle('padding:1px', 'heading'));
    }

    public function testScopedCssStyles(): void
    {
        $cssStyle = (new CssStyle())->setBlock($this->getBlock([
            'color' => 'red',
            'block' => ['margin' => '0'],
            'heading' => ['color' => 'blue'],
        ]));

        $this->assertSame('color:red;margin:0;', $cssStyle());
        $this->assertSame('color:blue;', $cssStyle('', 'heading'));
        $this->assertSame('', $cssStyle('', 'other'));
    }

    public function testNonScalarValuesAreSkippedAndNullRemovesDefault(): void
    {
        $cssStyle = (new CssStyle())->setBlock($this->getBlock([
            'block' => [
                'color' => null,
                'margin' => ['invalid'],
                'z-index' => 10,
            ],
        ]));

        $this->assertSame('padding:1px;z-index:10;', $cssStyle('color:red;padding:1px'));
    }

    public function testParserModifiesStyles(): void
    {
        $block = $this->getBlock(['color' => 'red']);

        $parser = $this->createMock(CssStyleParserInterface::class);
        $parser->expects($this->once())
            ->method('parse')
            ->with(['color' => 'red'], $block)
            ->willReturn(['color' => 'blue', 'display' => 'block']);

        $cssStyle = (new CssStyle([$parser]))->setBlock($block);
        $this->assertSame('color:blue;display:block;', $cssStyle());
    }

    public function testParsersAreCalledInOrderAndInvalidParsersAreSkipped(): void
    {
        $cssStyle = new CssStyle([
            $this->getAppendingParser('first', '1'),
            new stdClass(),
            $this->getAppendingParser('second', '2'),
        ]);
        $cssStyle->setBlock($this->getBlock([]));

        $this->assertSame('first:1;second:2;', $cssStyle());
    }

    private function getAppendingParser(string $name, string $value): CssStyleParserInterface
    {
        return new class($name, $value) implements CssStyleParserInterface {
            public function __construct(
                private readonly string $name,
                private readonly string $value
            ) {
            }

            public function parse(array $cssStyles, AbstractBlock $block): array
            {
                $cssStyles[$this->name] = $this->value;
                return $cssStyles;
            }
        };
    }

    private function getBlock(?array $cssStyles): Template
    {
        $block = $this->createMock(Template::class);
        $block->method('getData')->with('css_styles')->willReturn($cssStyles);

        return $block;
    }
}
