# Loki_CssUtils

**This is part of the core packages for Loki Components, as is being used by the Loki Checkout suite. It a**

## Installation
Install this package via composer:
```bash
composer require loki/magento2-css-utils
```

Next, enable this module:
```bash
bin/magento module:enable Loki_CssUtils
```

## Basic usage
Add the CSS utility to the PHP-section of your PHTML template:
```phtml
<?php
use Loki\CssUtils\Util\CssClass;

/** @var CssClass $css */
?>
<div class="<?= $css('') ?>">
    <h3 class="<?= $css('', 'heading') ?>">
        Hello World
    </h3>
</div>
```

Next, allow overriding CSS styles via XML layout. For instance, the following makes use of TailwindCSS utility classes:
```xml
<referenceBlock name="example">
    <arguments>
        <argument name="css_classes" xsi:type="array">
            <item name="block" xsi:type="array">
                <item name="default" xsi:type="string">m-4</item>
            </item>
            <item name="heading" xsi:type="array">
                <item name="default" xsi:type="string">text-4xl</item>
            </item>
        </argument>
    </arguments>
</referenceBlock>
```
