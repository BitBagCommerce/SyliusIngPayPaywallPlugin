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
bitbag_sylius_ing_pay_plugin:
    resource: "@BitBagSyliusIngPayPlugin/config/routes.yaml"
```

Import plugin configuration in `config/packages/_sylius.yaml` file:
```yaml
    imports:
        # ...
        - { resource: "@BitBagSyliusIngPayPlugin/config/config.yaml" }
```

## Known issues
### Translations not displaying correctly
For incorrectly displayed translations, execute the command:
```bash
bin/console cache:clear
```

### Can't create payment method (Invalid encryption key)

To create a new payment configuration, you need a valid encryption key. If you don’t have one, generate it by running:
```bash
bin/console sylius:payment:generate-key
```
