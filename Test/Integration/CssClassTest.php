<?php declare(strict_types=1);

namespace Loki\CssUtils\Test\Integration\Util;

use Loki\CssUtils\Util\CssClass;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class CssClassTest extends TestCase
{
    public function testWithNoChanges(): void
    {
        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block input0', $cssClass('input0'));
    }

    public function testWithTargetBlockOverride(): void
    {
        $this->getTargetBlock()->setCssClasses([
            'block' => [
                'default' => 'target1'
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block target1', $cssClass('input0'));
    }


    public function testWithGlobalBlockOverride(): void
    {
        $this->getTargetBlock()->setCssClasses([]);

        $this->getGlobalBlock()->setData([
            'example' => [
                'block' => [
                    'default' => 'default1'
                ]
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block default1', $cssClass('input0'));
    }

    public function testWithGlobalBlockExtendingTargetBlock(): void
    {
        $this->getTargetBlock()->setCssClasses([
            'block' => [
                'default' => 'target1'
            ]
        ]);

        $this->getGlobalBlock()->setData([
            'example' => [
                'block' => [
                    'extend' => 'default1'
                ]
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block target1 default1', $cssClass('input0'));
    }

    public function testWithGlobalBlockOverridingTargetBlock(): void
    {
        $this->getTargetBlock()->setCssClasses([
            'block' => [
                'default' => 'target1'
            ]
        ]);

        $this->getGlobalBlock()->setData([
            'example' => [
                'block' => [
                    'default' => 'default1'
                ]
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block default1', $cssClass('input0'));
    }

    public function testWithGlobalBlockExtendingAndTargetBlockExtending(): void
    {
        $this->getTargetBlock()->setCssClasses([
            'block' => [
                'extend1' => 'target1'
            ]
        ]);

        $this->getGlobalBlock()->setData([
            'example' => [
                'block' => [
                    'extend2' => 'default1'
                ]
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block input0 target1 default1', $cssClass('input0'));
    }

    public function testWithGlobalBlockOverridingTargetBlockExtending(): void
    {
        $this->getTargetBlock()->setCssClasses([
            'block' => [
                'extend1' => 'target1'
            ]
        ]);

        $this->getGlobalBlock()->setData([
            'example' => [
                'block' => [
                    'extend1' => 'default1'
                ]
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block input0 default1', $cssClass('input0'));
    }

    private function getTargetBlock(): Template
    {
        return $this->getBlock('example');
    }

    private function getGlobalBlock(): Template
    {
        return $this->getBlock('loki-components.css_classes');
    }

    private function getBlock(string $blockName): Template
    {
        $layout = $this->getLayout();
        $block = $layout->getBlock($blockName);
        if ($block instanceof Template) {
            return $block;
        }

        $block = $layout->createBlock(Template::class);
        $block->setNameInLayout($blockName);
        $block->setTemplate('dummy.phtml');
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
