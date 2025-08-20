<?php
declare(strict_types=1);

namespace Loki\CssUtils\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\Template;
use Loki\CssUtils\Util\CssClassFactory;
use Loki\CssUtils\Util\CssStyleFactory;

class AssignAdditionalBlockVariables implements ObserverInterface
{
    public function __construct(
        private readonly CssClassFactory $cssClassFactory,
        private readonly CssStyleFactory $cssStyleFactory
    ) {
    }

    public function execute(Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (false === $block instanceof Template) {
            return;
        }

        $cssClass = $this->cssClassFactory->create();
        $block->assign('css', $cssClass->setBlock($block));

        $cssStyle = $this->cssStyleFactory->create();
        $block->assign('style', $cssStyle->setBlock($block));
    }
}
