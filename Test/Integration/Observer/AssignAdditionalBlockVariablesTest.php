<?php declare(strict_types=1);

namespace Loki\CssUtils\Test\Integration\Observer;

use Loki\CssUtils\Observer\AssignAdditionalBlockVariables;
use Loki\CssUtils\Util\CssClass;
use Loki\CssUtils\Util\CssClassFactory;
use Loki\CssUtils\Util\CssStyle;
use Loki\CssUtils\Util\CssStyleFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event;
use Magento\Framework\Event\ConfigInterface as EventConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[AppArea('frontend')]
class AssignAdditionalBlockVariablesTest extends TestCase
{
    public function testObserverIsRegistered(): void
    {
        $eventConfig = ObjectManager::getInstance()->get(EventConfig::class);
        $observers = $eventConfig->getObservers('view_block_abstract_to_html_before');

        $this->assertArrayHasKey('Loki_CssUtils::assignAdditionalBlockVariables', $observers);
        $this->assertSame(
            AssignAdditionalBlockVariables::class,
            ltrim($observers['Loki_CssUtils::assignAdditionalBlockVariables']['instance'], '\\')
        );
    }

    public function testVariablesAreAssignedToTemplateBlock(): void
    {
        $block = $this->createLayout()->createBlock(Template::class, 'example');
        $this->dispatch($block);

        $viewVars = $this->getViewVars($block);
        $this->assertInstanceOf(CssClass::class, $viewVars['css'] ?? null);
        $this->assertInstanceOf(CssStyle::class, $viewVars['style'] ?? null);
        $this->assertSame('example scope-block foo', $viewVars['css']('foo'));
    }

    public function testEachBlockGetsItsOwnInstances(): void
    {
        $layout = $this->createLayout();
        $block1 = $layout->createBlock(Template::class, 'example1');
        $block2 = $layout->createBlock(Template::class, 'example2');
        $this->dispatch($block1);
        $this->dispatch($block2);

        $this->assertNotSame($this->getViewVars($block1)['css'], $this->getViewVars($block2)['css']);
        $this->assertSame('example1 scope-block', $this->getViewVars($block1)['css']());
        $this->assertSame('example2 scope-block', $this->getViewVars($block2)['css']());
    }

    public function testNonTemplateBlockIsSkipped(): void
    {
        $cssClassFactory = $this->createMock(CssClassFactory::class);
        $cssClassFactory->expects($this->never())->method('create');
        $cssStyleFactory = $this->createMock(CssStyleFactory::class);
        $cssStyleFactory->expects($this->never())->method('create');

        $block = $this->createLayout()->createBlock(Text::class, 'example');
        $observer = new AssignAdditionalBlockVariables($cssClassFactory, $cssStyleFactory);
        $observer->execute(new Observer(['event' => new Event(['block' => $block])]));
    }

    public function testTemplateRendersCssClassesAndStyles(): void
    {
        $layout = $this->createLayout();
        $block = $layout->createBlock(Template::class, 'loki-css-utils.test', [
            'data' => [
                'template' => 'Loki_CssUtils::test/example.phtml',
                'css_classes' => [
                    'block' => ['extend1' => 'local1'],
                    'heading' => ['default' => 'heading1'],
                ],
                'css_styles' => ['color' => 'red'],
            ],
        ]);

        $html = $block->toHtml();
        $this->assertStringContainsString(
            'class="loki-css-utils-test scope-block loki-test-default local1"',
            $html
        );
        $this->assertStringContainsString('style="color:red;"', $html);
        $this->assertStringContainsString(
            'class="loki-css-utils-test__heading scope-heading heading1"',
            $html
        );
    }

    private function dispatch(object $block): void
    {
        ObjectManager::getInstance()->get(EventManager::class)
            ->dispatch('view_block_abstract_to_html_before', ['block' => $block]);
    }

    private function getViewVars(Template $block): array
    {
        $property = new ReflectionProperty(Template::class, '_viewVars');

        return (array)$property->getValue($block);
    }

    private function createLayout(): LayoutInterface
    {
        return ObjectManager::getInstance()->create(LayoutInterface::class);
    }
}
