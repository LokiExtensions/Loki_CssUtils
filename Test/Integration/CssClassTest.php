<?php declare(strict_types=1);

namespace Loki\CssUtils\Test\Integration\Util;

use Loki\CssUtils\Util\CssClass;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class CssClassTest extends TestCase
{
    /*public function testWithNoAdditions()
    {
        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block test0', $cssClass('test0'));
    }

    public function testWithCssClassesOverride()
    {
        $this->getTargetBlock()->setCssClasses([
            'block' => [
                'default' => 'test2'
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block test2', $cssClass('test0'));
    }*/

    public function testWithDefaultCssClassesIgnored()
    {
        $this->getTargetBlock()->setCssClasses([
            'block' => [
                'default' => 'test1'
            ]
        ]);

        $this->getDefaultBlock()->setData([
            'example' => [
                'default' => 'test2'
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block test1', $cssClass('test0'));
    }

    public function testWithDefaultCssClassesOverride()
    {
        $this->getTargetBlock()->setCssClasses([
            'block' => [
                'default' => 'test1'
            ]
        ]);

        $this->getDefaultBlock()->setData([
            'example' => [
                'override' => 'test2'
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block test2', $cssClass('test0'));
    }

    /*
    public function testWithCssClassesExtend()
    {
        $this->getTargetBlock()->setCssClasses([
            'block' => [
                'additional' => 'test1'
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block test0 test1', $cssClass('test0'));
    }

    public function testWithDefaultCssClassesExtend()
    {
        $this->getTargetBlock()->setCssClasses([
            'block' => [
                'additional' => 'test1'
            ]
        ]);

        $this->getDefaultBlock()->setCssClasses([
            'example' => [
                'test2'
            ]
        ]);

        $cssClass = $this->getCssClass();
        $this->assertEquals('example scope-block test0 test1 test2', $cssClass('test0'));
    }*/

    private function getTargetBlock(): Template
    {
        return $this->getBlock('example');
    }

    private function getDefaultBlock(): Template
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
        $cssClass = new CssClass();
        $cssClass->setBlock($this->getTargetBlock());
        return $cssClass;
    }

    private function getLayout(): LayoutInterface
    {
        return ObjectManager::getInstance()->get(LayoutInterface::class);
    }
}
