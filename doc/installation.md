# Installation

## Overview:
GENERAL
- [Requirements](#requirements)
- [Composer](#composer)
- [Basic configuration](#basic-configuration)
---
ADDITIONAL
- [Known Issues](#known-issues)
---

## Requirements:
We work on stable, supported and up-to-date versions of packages. We recommend you to do the same.

| Package       | Version          |
|---------------|------------------|
| PHP           | \>=8.1           |
| sylius/sylius | 1.13.x - 1.14.x  |
| Symfony       | 5.4 \|\| 6.4     |
| MySQL         | \>= 5.7          |
| NodeJS        | \>= 18.x         |

## Composer:
```bash
composer require bitbag/ing-pay-paywall-plugin --with-all-dependencies
```

## Basic configuration:
Add plugin dependencies to your `config/bundles.php` file:

```php
# config/bundles.php

return [
    ...
    BitBag\SyliusIngPayPlugin\BitBagSyliusIngPayPlugin::class => ['all' => true],
];
```

Add routing to your `config/routes.yaml` file:
```yaml
# config/routes.yaml

bitbag_sylius_ing_pay_plugin:
    resource: "@BitBagSyliusIngPayPlugin/config/routing.yml"
```

## Known issues
### Translations not displaying correctly
For incorrectly displayed translations, execute the command:
```bash
bin/console cache:clear
```
