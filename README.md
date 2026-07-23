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

## Comparison with Hyva `$block->getCssClasses()` approach
From Hyva 1.4, templates started to include block methods like `getCssClasses()` which resembles the approach of the Loki CSS Utils. Here is a comparison between the two:

The Hyva `getCssClasses()` method simply returns string, which is defined as such in the XML layout. You either use the XML layout to define a specific CSS class, or you use the template-based value. But not both. The Loki `$css()` allows you to both extend and override default values (via the `scope`).

The Hyva `getCssClasses()` method by default just relies upon input from the XML layout. You could write an interceptor or observer to modify the values on the fly, but there is no guidance on how to do this. Yet, the `$css()` uses a construction of CSS Class parsers (implementing `\Loki\CssUtils\Util\CssClassParser\CssClassParserInterface`) which easily allows for more advanced scenarios like sorting classes or merging them (`text-2xl` plus `text-xl` becomes `text-xl`).

The Hyva approach only allows the block itself to be the source of definitions. Loki adds the option for using a global block and block groups, so that multiple blocks (and templates) are targeted at once. And each of those generic blocks again allows for extending and/or overriding existing values.

As we see it, the basics of both Hyva approach and Loki approach are the same. Loki simply took things much further.

## Documentation
See for more usage [https://loki-checkout.com/](https://loki-checkout.com/)
