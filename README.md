# Loki_CssUtils

**This Magento 2 module is part of the core packages for Loki Components, as is being used by the Loki Checkout, Loki Admin Components and Loki Theme for Luma. This stand-alone module allows a Magento 2 template to call `$css()` and `$style()` to generate CSS classes and CSS styles, that are easily overwritten via XML layout and/or PHP parser classes - without template overrides.**

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

## Defining CSS classes in the global block
Instead of adding CSS classes per block, you can also move the CSS classes to a global `loki_components.css_classes` block.

```xml
<referenceBlock name="loki-components.css_classes">
    <arguments>
        <argument name="example" xsi:type="array">
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

## Defining CSS classes via CSS class groups
Yet another option is to allow for each block to be using one or more CSS class groups. Each CSS class group (for example `foobar`) corresponds to a block prefixed with `loki-components.css_classes.` (for example `loki-components.css_classes.foobar`).

```xml
<referenceBlock name="example">
    <arguments>
        <argument name="css_class_groups" xsi:type="array">
            <item name="example-group-1" xsi:type="string">example-group-1</item>
            <item name="example-group-2" xsi:type="string">example-group-2</item>
        </argument>
    </arguments>
</referenceBlock>
```

And:

```xml
<block name="loki-components.css_classes.example-group-1">
    <arguments>
        <argument name="block" xsi:type="array">
            <item name="default" xsi:type="string">m-4</item>
        </argument>
    </arguments>
</block>

<block name="loki-components.css_classes.example-group-2">
    <arguments>
        <argument name="heading" xsi:type="array">
            <item name="default" xsi:type="string">text-4xl</item>
        </argument>
    </arguments>
</block>
```

## Documentation
See for more usage [https://loki-checkout.com/](https://loki-checkout.com/)
