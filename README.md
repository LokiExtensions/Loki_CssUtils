# Loki_CssUtils

**This is part of the core packages for Loki Components, as is being used by the Loki Checkout suite. It a**

## Installation
Install this package via composer (assuming you have setup the `composer.yireo.com` repository correctly already):
```bash
composer require loki/magento2-css-utils
```

Next, enable this module:
```bash
bin/magento module:enable Loki_CssUtils
```

## Basic usage
Add the CSS utility to your own PHTML templates:
```phtml
use Loki\CssUtils\Util\CssClass;
use Loki\CssUtils\Util\CssStyle;

```

Next, allow overriding CSS styles via XML layout:
```xml
<referenceBlock name="example">
    <arguments>
        <argument name="css_classes" xsi:type=""
    </arguments>
</referenceBlock>
```
