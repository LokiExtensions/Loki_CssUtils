<?php declare(strict_types=1);

namespace Loki\CssUtils\Test\Integration\Layout;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class DefaultLayoutTest extends TestCase
{
    public function testCssPropertiesBlockIsAddedToDefaultHandle(): void
    {
        $layout = $this->loadLayout(['default']);

        $block = $layout->getBlock('loki.css-properties');
        $this->assertInstanceOf(Template::class, $block);
        $this->assertSame('Loki_CssUtils::stylesheet/css-properties.phtml', $block->getTemplate());
        $this->assertSame('before.body.end', $layout->getParentName('loki.css-properties'));
    }

    public function testTestHandleAddsExampleBlock(): void
    {
        $layout = $this->loadLayout(['default', 'loki_css_utils_test']);

        $block = $layout->getBlock('loki-css-utils.test');
        $this->assertInstanceOf(Template::class, $block);
        $this->assertSame('Loki_CssUtils::test/example.phtml', $block->getTemplate());
        $cssPropertiesBlock = $layout->getBlock('loki.css-properties');
        $this->assertInstanceOf(Template::class, $cssPropertiesBlock);
        $this->assertSame(
            ['loki-css-utils-test' => '42px'],
            $cssPropertiesBlock->getData('css_properties')
        );

        $html = $block->toHtml();
        $this->assertStringContainsString(
            'class="loki-css-utils-test scope-block loki-test-default loki-test-local loki-test-global loki-test-template-group"',
            $html
        );
        $this->assertStringContainsString(
            'class="loki-css-utils-test__heading scope-heading loki-test-heading-override loki-test-group"',
            $html
        );
        $this->assertStringContainsString(
            'class="loki-css-utils-test__forced scope-forced loki-test-forced"',
            $html
        );
        $this->assertStringContainsString('style="color:rgb(255, 0, 0);margin-top:7px;"', $html);
    }

    public function testLayoutFilesUseValidSchemaLocation(): void
    {
        $layoutFiles = glob(dirname(__DIR__, 3) . '/view/frontend/layout/*.xml');
        $this->assertNotEmpty($layoutFiles);

        foreach ($layoutFiles as $layoutFile) {
            $xml = simplexml_load_file($layoutFile);
            $schemaLocation = (string)$xml->attributes('http://www.w3.org/2001/XMLSchema-instance')
                ->noNamespaceSchemaLocation;

            $this->assertSame(
                'urn:magento:framework:View/Layout/etc/page_configuration.xsd',
                $schemaLocation,
                basename($layoutFile)
            );
        }
    }

    private function loadLayout(array $handles): LayoutInterface
    {
        $objectManager = ObjectManager::getInstance();
        $objectManager->get(DesignInterface::class)->setDesignTheme('Magento/luma', 'frontend');

        /** @var Page $page */
        $page = $objectManager->get(PageFactory::class)->create(true);
        $page->getConfig()->setPageLayout('1column');
        $page->addHandle($handles);

        $layout = $page->getLayout();
        $layout->getBlock('root');

        return $layout;
    }
}
