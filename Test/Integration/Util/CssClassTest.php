<?php declare(strict_types=1);

namespace Loki\CssUtils\Test\Integration\Util;

use Loki\CssUtils\Util\CssClass;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

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
