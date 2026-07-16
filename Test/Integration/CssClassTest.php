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
        string $inputCssClass,
        array $targetBlockCssClasses,
        array $globalBlockCssClasses,
        array $groupBlockCssClasses,
        array $legacyGroupBlockCssClasses,
        string $expectedCssClass
    ): void {
        $this->getTargetBlock()->setCssClasses($targetBlockCssClasses);
        $this->getGlobalBlock()->setData($globalBlockCssClasses);
        $this->getGroupBlock('example-template')->setData($groupBlockCssClasses);
        $this->getLegacyGroupBlock('example-template')->setCssClasses($legacyGroupBlockCssClasses);

        $cssClass = $this->getCssClass();
        $this->assertEquals($expectedCssClass, $cssClass($inputCssClass));
    }

    public static function getTestData(): array
    {
        return [
            [
                'input0',
                [],
                [],
                [],
                [],
                'example scope-block input0'
            ], // No changes made
            [
                'input0',
                [
                    'block' => [
                        'default' => 'target1'
                    ]
                ],
                [],
                [],
                [],
                'example scope-block target1'
            ], // Simple per-block override
            [
                'input0',
                [],
                [
                    'example' => [
                        'block' => [
                            'default' => 'default1'
                        ]
                    ]
                ],
                [],
                [],
                'example scope-block default1'
            ], // Simple global override
            [
                'input0',
                [
                    'block' => [
                        'default' => 'target1'
                    ]
                ],
                [
                    'example' => [
                        'block' => [
                            'extend' => 'default1'
                        ]
                    ]
                ],
                [],
                [],
                'example scope-block target1 default1'
            ], // Per-block override with global extend
            [
                'input0',
                [
                    'block' => [
                        'default' => 'target1'
                    ]
                ],
                [
                    'example' => [
                        'block' => [
                            'default' => 'default1'
                        ]
                    ]
                ],
                [],
                [],
                'example scope-block default1'
            ], // Per-block override with global override
            [
                'input0',
                [
                    'block' => [
                        'extend1' => 'target1'
                    ]
                ],
                [
                    'example' => [
                        'block' => [
                            'extend2' => 'default1'
                        ]
                    ]
                ],
                [],
                [],
                'example scope-block input0 target1 default1'
            ], // Both per-block extend and global extend
            [
                'input0',
                [
                    'block' => [
                        'extend1' => 'target1'
                    ]
                ],
                [
                    'example' => [
                        'block' => [
                            'extend1' => 'default1'
                        ]
                    ]
                ],
                [],
                [],
                'example scope-block input0 default1'
            ], // Per-block extend and global override of that extend
            [
                'input0',
                [
                    'block' => [
                        'extend1' => 'target1'
                    ]
                ],
                [
                    'example' => [
                        'block' => [
                            'extend2' => 'default1'
                        ]
                    ]
                ],
                [
                    'block' => [
                        'extend3' => 'group1'
                    ]
                ],
                [],
                'example scope-block input0 target1 default1 group1'
            ], // Per-block extend and global extend and group extend
            [
                'input0',
                [
                    'block' => [
                        'extend1' => 'target1'
                    ]
                ],
                [
                    'example' => [
                        'block' => [
                            'extend2' => 'default1'
                        ]
                    ]
                ],
                [
                    'block' => [
                        'extend3' => 'group1'
                    ]
                ],
                [
                    'block' => [
                        'extend4' => 'legacy1'
                    ]
                ],
                'example scope-block input0 target1 default1 legacy1 group1'
            ], // Per-block extend and global extend and group extend and legacy extend
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
                'template' => 'example-template.phtml'
            ]
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
