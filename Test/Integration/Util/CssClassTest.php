<?php declare(strict_types=1);

namespace Loki\CssUtils\Test\Integration\Util;

use Loki\CssUtils\Util\CssClass;
use Loki\CssUtils\Util\CssClassParser\CssClassParserInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class CssClassTest extends TestCase
{
    #[DataProvider('getTestData')]
    public function testWithNoChanges(
        CssClassTestInput $input,
    ): void {
        $this->getTargetBlock()->setCssClasses(['block' => $input->targetBlockCssClasses]);
        $this->getGlobalBlock()->setData(['example' => ['block' => $input->globalBlockCssClasses]]);
        $this->getGroupBlock('example-template')->setData(['block' => $input->groupBlockCssClasses]);
        $this->getLegacyGroupBlock('example-template')->setCssClasses(['block' => $input->legacyGroupBlockCssClasses]);

        $cssClass = $this->getCssClass();
        $this->assertEquals($input->expectedCssClass, $cssClass($input->inputCssClass));
    }

    public static function getTestData(): array
    {
        return [
            [
                new CssClassTestInput(
                    expectedCssClass: 'example scope-block input0'
                ),
            ], // No changes made
            [
                new CssClassTestInput(
                    targetBlockCssClasses: ['default' => 'target1',],
                    expectedCssClass: 'example scope-block target1'
                ),
            ], // Simple per-block override
            [
                new CssClassTestInput(
                    globalBlockCssClasses: ['default' => 'default1',],
                    expectedCssClass: 'example scope-block default1'
                ),
            ], // Simple global override
            [
                new CssClassTestInput(
                    targetBlockCssClasses: ['default' => 'target1',],
                    globalBlockCssClasses: ['extend' => 'default1',],
                    expectedCssClass: 'example scope-block target1 default1'
                ),
            ], // Per-block override with global extend
            [
                new CssClassTestInput(
                    targetBlockCssClasses: ['default' => 'target1',],
                    globalBlockCssClasses: ['default' => 'default1',],
                    expectedCssClass: 'example scope-block default1'
                ),
            ], // Per-block override with global override
            [
                new CssClassTestInput(
                    targetBlockCssClasses: ['extend1' => 'target1',],
                    globalBlockCssClasses: ['extend2' => 'default1',],
                    expectedCssClass: 'example scope-block input0 target1 default1'
                ),
            ], // Both per-block extend and global extend
            [
                new CssClassTestInput(
                    targetBlockCssClasses: ['extend1' => 'target1',],
                    globalBlockCssClasses: ['extend1' => 'default1',],
                    expectedCssClass: 'example scope-block input0 default1'
                ),
            ], // Per-block extend and global override of that extend
            [
                new CssClassTestInput(
                    targetBlockCssClasses: ['extend1' => 'target1',],
                    globalBlockCssClasses: ['extend2' => 'default1',],
                    groupBlockCssClasses: ['extend3' => 'group1',],
                    expectedCssClass: 'example scope-block input0 target1 default1 group1'
                ),
            ], // Per-block extend and global extend and group extend
            [
                new CssClassTestInput(
                    targetBlockCssClasses: ['extend1' => 'target1',],
                    globalBlockCssClasses: ['extend2' => 'default1',],
                    groupBlockCssClasses: ['extend3' => 'group1',],
                    legacyGroupBlockCssClasses: ['extend4' => 'legacy1',],
                    expectedCssClass: 'example scope-block input0 target1 default1 legacy1 group1'
                ),
            ], // Per-block extend and global extend and group extend and legacy extend
            [
                new CssClassTestInput(
                    targetBlockCssClasses: ['extend1' => 'target1',],
                    globalBlockCssClasses: ['extend2' => 'default1',],
                    groupBlockCssClasses: ['default' => 'group1',],
                    legacyGroupBlockCssClasses: ['extend4' => 'legacy1',],
                    expectedCssClass: 'example scope-block group1 target1 default1 legacy1'
                ),
            ], // Per-block extend and global extend and group override and legacy extend
            [
                new CssClassTestInput(
                    targetBlockCssClasses: ['extend1' => 'target1',],
                    globalBlockCssClasses: ['extend2' => 'default1',],
                    groupBlockCssClasses: ['_force' => 'group1',],
                    legacyGroupBlockCssClasses: ['extend4' => 'legacy1',],
                    expectedCssClass: 'example scope-block group1'
                ),
            ], // Force group override
        ];
    }

    public function testWithFlatStringCssClasses(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', ['css_classes' => 'flat1 flat2']);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block flat1 flat2', $cssClass('input0'));
    }

    public function testWithNonBlockScope(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', [
            'css_classes' => [
                'block' => ['default' => 'block1'],
                'heading' => ['extend1' => 'heading1'],
            ],
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example__heading scope-heading input0 heading1', $cssClass('input0', 'heading'));
        $this->assertSame('example scope-block block1', $cssClass('input0'));
        $this->assertSame('example__other scope-other input0', $cssClass('input0', 'other'));
    }

    public function testWithForceInNonBlockScope(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', [
            'css_classes' => [
                'heading' => ['extend1' => 'heading1', '_force' => 'forced1'],
            ],
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example__heading scope-heading forced1', $cssClass('input0', 'heading'));
    }

    #[DataProvider('getCssNameData')]
    public function testCssName(string $nameInLayout, ?string $cssName, string $expectedCssClass): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, $nameInLayout, ['css_name' => $cssName]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame($expectedCssClass, $cssClass('input0'));
    }

    public static function getCssNameData(): array
    {
        return [
            ['foo.bar_baz', null, 'foo-bar-baz scope-block input0'],
            ['Foo.Bar', null, 'foo-bar scope-block input0'],
            ['foo.bar', 'custom-name', 'custom-name scope-block input0'],
        ];
    }

    public function testCssClassGroups(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', [
            'css_class_groups' => ['group-a', 'group-b'],
        ]);

        $this->createBlock($layout, 'loki-components.css_classes.group-a', [
            'block' => ['extend-a' => 'group-a1'],
        ]);
        $this->createBlock($layout, 'loki-components.css_classes.group-b', [
            'heading' => ['extend-b' => 'group-b1'],
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block input0 group-a1', $cssClass('input0'));
        $this->assertSame('example__heading scope-heading input0 group-b1', $cssClass('input0', 'heading'));
    }

    public function testMultipleCssClassGroupsExtendSameScope(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', [
            'css_class_groups' => ['group-a', 'group-b'],
        ]);

        $this->createBlock($layout, 'loki-components.css_classes.group-a', [
            'block' => ['extend-a' => 'group-a1'],
        ]);
        $this->createBlock($layout, 'loki-components.css_classes.group-b', [
            'block' => ['extend-b' => 'group-b1'],
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block input0 group-a1 group-b1', $cssClass('input0'));
    }

    public function testMultipleCssClassGroupsOverrideSameKey(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', [
            'css_class_groups' => ['group-a', 'group-b'],
        ]);

        $this->createBlock($layout, 'loki-components.css_classes.group-a', [
            'block' => ['extend-x' => 'group-a1'],
        ]);
        $this->createBlock($layout, 'loki-components.css_classes.group-b', [
            'block' => ['extend-x' => 'group-b1'],
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block input0 group-b1', $cssClass('input0'));
    }

    public function testMultipleLegacyCssClassGroupsExtendSameScope(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', [
            'css_class_groups' => ['group-a', 'group-b'],
        ]);

        $this->createBlock($layout, 'loki-components.defaults.group-a', [
            'css_classes' => ['block' => ['extend-a' => 'legacy-a1']],
        ]);
        $this->createBlock($layout, 'loki-components.defaults.group-b', [
            'css_classes' => ['block' => ['extend-b' => 'legacy-b1']],
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block input0 legacy-a1 legacy-b1', $cssClass('input0'));
    }

    public function testMultipleLegacyCssClassGroupsOverrideSameKey(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', [
            'css_class_groups' => ['group-a', 'group-b'],
        ]);

        $this->createBlock($layout, 'loki-components.defaults.group-a', [
            'css_classes' => ['block' => ['extend-x' => 'legacy-a1']],
        ]);
        $this->createBlock($layout, 'loki-components.defaults.group-b', [
            'css_classes' => ['block' => ['extend-x' => 'legacy-b1']],
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block input0 legacy-b1', $cssClass('input0'));
    }

    #[DataProvider('getNonStringValueData')]
    public function testNonStringValuesAreIgnored(mixed $value): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', [
            'css_classes' => ['block' => ['extend1' => $value]],
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block input0', $cssClass('input0'));
    }

    public static function getNonStringValueData(): array
    {
        return [
            [null],
            [true],
            [false],
            [42],
        ];
    }

    public function testNullValueRemovesExtendedCssClass(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', [
            'css_classes' => ['block' => ['extend1' => 'target1']],
        ]);
        $this->createBlock($layout, 'loki-components.css_classes', [
            'example' => ['block' => ['extend1' => null]],
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block input0', $cssClass('input0'));
    }

    #[DataProvider('getTemplateIdData')]
    public function testCssClassGroupFromTemplateId(string $template, string $groupId): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', ['template' => $template]);
        $this->createBlock($layout, 'loki-components.css_classes.' . $groupId, [
            'block' => ['extend1' => 'template1'],
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block input0 template1', $cssClass('input0'));
    }

    public static function getTemplateIdData(): array
    {
        return [
            ['Vendor_Module::foo/bar.phtml', 'foo.bar'],
            ['Vendor_Module::foo.phtml', 'foo'],
            ['foo/bar/baz.phtml', 'foo.bar.baz'],
        ];
    }

    public function testInvalidGroupValuesAreIgnored(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', ['css_class_groups' => ['group-a']]);
        $this->createBlock($layout, 'loki-components.css_classes.group-a', [
            'block' => [],
            'heading' => 'not-an-array',
        ]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block input0', $cssClass('input0'));
        $this->assertSame('example__heading scope-heading input0', $cssClass('input0', 'heading'));
    }

    public function testMissingGroupBlockIsIgnored(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', ['css_class_groups' => ['does-not-exist']]);

        $cssClass = (new CssClass($layout))->setBlock($block);
        $this->assertSame('example scope-block input0', $cssClass('input0'));
    }

    public function testCssClassParsers(): void
    {
        $layout = $this->createLayout();
        $block = $this->createBlock($layout, 'example', [
            'css_classes' => ['heading' => ['extend1' => 'heading1']],
        ]);

        $parser = new class implements CssClassParserInterface {
            public array $calls = [];

            public function parse(array $cssClasses, string $scope, AbstractBlock $block): array
            {
                $this->calls[] = [$cssClasses, $scope, $block];
                $cssClasses['parsed'] = 'parsed1';
                return $cssClasses;
            }
        };

        $cssClass = new CssClass($layout, [new stdClass(), $parser]);
        $cssClass->setBlock($block);

        $this->assertSame('example__heading scope-heading input0 heading1 parsed1', $cssClass('input0', 'heading'));
        $this->assertCount(1, $parser->calls);
        $this->assertSame(['default' => 'input0', 'extend1' => 'heading1'], $parser->calls[0][0]);
        $this->assertSame('heading', $parser->calls[0][1]);
        $this->assertSame($block, $parser->calls[0][2]);
    }

    private function createLayout(): LayoutInterface
    {
        return ObjectManager::getInstance()->create(LayoutInterface::class);
    }

    private function createBlock(LayoutInterface $layout, string $blockName, array $data = []): Template
    {
        /** @var Template $block */
        $block = $layout->createBlock(Template::class, $blockName, ['data' => $data]);

        return $block;
    }

    private function getTargetBlock(): Template
    {
        return $this->getBlock('example');
    }

    private function getGlobalBlock(): Template
    {
        return $this->getBlock('loki-components.css_classes');
    }

    private function getGroupBlock(string $groupId): Template
    {
        return $this->getBlock('loki-components.css_classes.'.$groupId);
    }

    private function getLegacyGroupBlock(string $groupId): Template
    {
        return $this->getBlock('loki-components.defaults.'.$groupId);
    }

    private function getBlock(string $blockName): Template
    {
        $layout = $this->getLayout();
        $block = $layout->getBlock($blockName);
        if ($block instanceof Template) {
            return $block;
        }

        /** @var Template $block */
        $block = $layout->createBlock(Template::class, '', [
            'data' => [
                'template' => 'example-template.phtml',
            ],
        ]);

        $block->setNameInLayout($blockName);

        return $block;
    }

    private function getCssClass(): CssClass
    {
        $cssClass = new CssClass($this->getLayout());
        $cssClass->setBlock($this->getTargetBlock());

        return $cssClass;
    }

    private function getLayout(): LayoutInterface
    {
        return ObjectManager::getInstance()->get(LayoutInterface::class);
    }
}
