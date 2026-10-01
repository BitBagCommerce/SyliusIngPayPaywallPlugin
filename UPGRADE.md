# UPGRADE FROM `v1.0.x` TO `v1.1.0`

This is a breaking release. The plugin is rebranded from imoje to ING Pay (the same rebranding
applied to the 2.x line in `v2.0.2`/`v3.0.0`) and Sylius 1.14 is now supported.

Sections 3, 4 and 5 require manual action. If you skip section 4, customers will be charged
without their orders being marked as paid.

## 1. Package name

```bash
composer remove bitbag/imoje-paywall-plugin
composer require bitbag/ing-pay-paywall-plugin --with-all-dependencies
```

## 2. Renames

| Element | Before | After |
|---|---|---|
| namespace | `BitBag\SyliusImojePlugin` | `BitBag\SyliusIngPayPlugin` |
| test namespace | `Tests\BitBag\SyliusImojePlugin` | `Tests\BitBag\SyliusIngPayPlugin` |
| bundle class | `BitBagSyliusImojePlugin` | `BitBagSyliusIngPayPlugin` |
| DI extension | `BitBagSyliusImojeExtension` | `BitBagSyliusIngPayExtension` |
| gateway factory | `ImojeGatewayFactory` | `IngPayGatewayFactory` |
| API class / interface | `ImojeApi` / `ImojeApiInterface` | `IngPayApi` / `IngPayApiInterface` |
| gateway config form | `ImojeGatewayConfigurationType` | `IngPayGatewayConfigurationType` |
| service id prefix | `bitbag.imoje_plugin.*` | `bitbag.ing_pay_plugin.*` |
| notify controller service | `bitbag.sylius_imoje_plugin.controller.notify_controller` | `bitbag.sylius_ing_pay_plugin.controller.notify_controller` |
| notify controller method | `verifyImojeNotification` | `verifyIngPayNotification` |
| route name | `bitbag_sylius_imoje_payment_notify` | `bitbag_sylius_ing_pay_payment_notify` |
| translation prefix | `bitbag.imoje_plugin.*` | `bitbag.ing_pay_plugin.*` |
| gateway label key | `bitbag.imoje_plugin.gateway_label` | `bitbag.ing_pay_plugin.paywall` |

Update `config/bundles.php`:

```php
return [
    // ...
    BitBag\SyliusIngPayPlugin\BitBagSyliusIngPayPlugin::class => ['all' => true],
];
```

Update `config/routes.yaml`:

```yaml
bitbag_sylius_ing_pay_plugin:
    resource: "@BitBagSyliusIngPayPlugin/config/routing.yml"
```

## 3. Gateway factory name changed, data migration required

The Payum gateway identifier changed from `imoje` to `ing_pay_paywall` on the
`payum.gateway_factory_builder`, `payum.action` and `sylius.gateway_configuration_type` tags.

Existing shops have `imoje` stored in the database. Until this migration is run, the existing
payment method no longer maps to any gateway factory and every payment through it fails with
`Gateway "imoje" does not exist.`:

```sql
UPDATE sylius_gateway_config SET factory_name = 'ing_pay_paywall' WHERE factory_name = 'imoje';
```

`gateway_name` does not need to be changed. The 2.x line ships no Doctrine migration for this,
so none is shipped here either. Run the statement manually or wrap it in your own project
migration as part of the deployment.

## 4. Webhook path changed, ING panel must be reconfigured

| | |
|---|---|
| Before | `/payment/imoje/notify` |
| After | `/payment/ing_pay/notify` |

There is no backwards-compatible alias, which follows the 2.x line.

After deploying, change the notification URL in the imoje/ING merchant panel
(`Shops` -> your shop -> `Details` -> `Integration data`) to
`https://<your-shop>/payment/ing_pay/notify`.

Until you do, ING keeps sending notifications to the old path, the shop answers `404`, and no
payment is ever confirmed: money is taken from the customer while the order stays unpaid. Plan
the SQL migration, the deployment and the panel change as one maintenance window.

## 5. `statusImoje` key in `sylius_payment.details`

The key the plugin writes into the payment details payload was renamed from `statusImoje` to
`statusIngPay`. New payments are unaffected. For payments already in the database:

```sql
UPDATE sylius_payment
SET details = JSON_SET(JSON_REMOVE(details, '$.statusImoje'), '$.statusIngPay', JSON_UNQUOTE(JSON_EXTRACT(details, '$.statusImoje')))
WHERE JSON_EXTRACT(details, '$.statusImoje') IS NOT NULL;
```

## 6. What did not change

These belong to the payment provider's protocol and were not renamed by ING, in this line or
in 2.x:

* `https://paywall.imoje.pl/payment` and `https://sandbox.paywall.imoje.pl/payment`
* the `X-Imoje-Signature` header and the `sha256` signature algorithm
* the merchant panel URLs `https://imoje.ing.pl` and `https://sandbox.imoje.ing.pl`

The gateway configuration fields (`environment`, `merchant_id`, `service_id`, `service_key`,
`authorization_token`) are unchanged, so credentials stored in `sylius_gateway_config.config`
do not need to be touched.

## 7. Requirements

| | Before (`v1.0.3`) | After (`v1.1.0`) |
|---|---|---|
| `php` | `^8.0` | `^8.1` |
| `sylius/sylius` | `~1.12.0 \|\| ~1.13.0` | `~1.13.0 \|\| ~1.14.0` |

Sylius 1.12 and PHP 8.0 support are dropped, matching how the other BitBag plugins moved onto
the 1.14 line. If you are still on Sylius 1.12, upgrade to 1.13 first using `v1.0.3` and then
take this release.

---

# UPGRADE FROM `v1.3.X` TO `v1.4.0`

First step is upgrading Sylius with composer

- `composer require sylius/sylius:~1.4.0`

### Test application database

#### Migrations

If you provide migrations with your plugin, take a look at following changes:

* Change base `AbstractMigration` namespace to `Doctrine\Migrations\AbstractMigration`
* Add `: void` return types to both `up` and `down` functions

#### Schema update

If you don't use migrations, just run `(cd tests/Application && bin/console doctrine:schema:update --force)` to update the test application's database schema.

### Dotenv

* `composer require symfony/dotenv:^4.2 --dev`
* Follow [Symfony dotenv update guide](https://symfony.com/doc/current/configuration/dot-env-changes.html) to incorporate required changes in `.env` files structure. Remember - they should be done on `tests/Application/` level! Optionally, you can take a look at [corresponding PR](https://github.com/Sylius/PluginSkeleton/pull/156/) introducing these changes in **PluginSkeleton** (this PR also includes changes with Behat - see below)

Don't forget to clear the cache (`tests/Application/bin/console cache:clear`) to be 100% everything is loaded properly.

### Test application kernel

The kernel of the test application needs to be replaced with this [file](https://github.com/Sylius/PluginSkeleton/blob/1.4/tests/Application/Kernel.php).
The location of the kernel is: `tests/Application/Kernel.php` (replace the content with the content of the file above).
The container cleanup method is removed in the new version and keeping it will cause problems with for example the `TagAwareAdapter` which will call `commit()` on its pool from its destructor. If its pool is `TraceableAdapter` with pool `ArrayAdapter`, then the pool property of `TraceableAdapter` will be nullified before the destructor is executed and cause an error.

---

### Behat

If you're using Behat and want to be up-to-date with our configuration

* Update required extensions with `composer require friends-of-behat/symfony-extension:^2.0 friends-of-behat/page-object-extension:^0.3 --dev`
* Remove extensions that are not needed yet with `composer remove friends-of-behat/context-service-extension friends-of-behat/cross-container-extension friends-of-behat/service-container-extension --dev`
* Update your `behat.yml` - look at the diff [here](https://github.com/Sylius/Sylius-Standard/pull/322/files#diff-7bde54db60a6e933518d8b61b929edce)
* Add `SymfonyExtensionBundle` to your `tests/Application/config/bundles.php`:
    ```php
    return [
        //...
        FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle::class => ['test' => true, 'test_cached' => true],
    ];
    ```
* If you use our Travis CI configuration, follow [these changes](https://github.com/Sylius/PluginSkeleton/pull/156/files#diff-354f30a63fb0907d4ad57269548329e3) introduced in `.travis.yml` file
* Create `tests/Application/config/services_test.yaml` file with the following code and add these your own Behat services as well:
    ```yaml
    imports:
        - { resource: "../../../vendor/sylius/sylius/src/Sylius/Behat/Resources/config/services.xml" }
    ```
* Remove all `__symfony__` prefixes in your Behat services
* Remove all `<tag name="fob.context_service" />` tags from your Behat services
* Make your Behat services public by default with `<defaults public="true" />`
* Change `contexts_services ` in your suite definitions to `contexts`
* Take a look at [SymfonyExtension UPGRADE guide](https://github.com/FriendsOfBehat/SymfonyExtension/blob/master/UPGRADE-2.0.md) if you have any more problems

### Phpstan

* Fix the container XML path parameter in the `phpstan.neon` file as done [here](https://github.com/Sylius/PluginSkeleton/commit/37fa614dbbcf8eb31b89eaf202b4bd4d89a5c7b3)

# UPGRADE FROM `v1.2.X` TO `v1.4.0`

Firstly, check out the [PluginSkeleton 1.3 upgrade guide](https://github.com/Sylius/PluginSkeleton/blob/1.4/UPGRADE-1.3.md) to update Sylius version step by step.
To upgrade to Sylius 1.4 follow instructions from [the previous section](https://github.com/Sylius/PluginSkeleton/blob/1.4/UPGRADE-1.4.md#upgrade-from-v13x-to-v140) with following changes:

### Doctrine migrations

* Change namespaces of copied migrations to `Sylius\Migrations`

### Dotenv

* These changes are not required, but can be done as well, if you've changed application directory structure in `1.2.x` to `1.3` update

### Behat

* Add `\FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle()` to your bundles lists in `tests/Application/AppKernel.php` (preferably only in `test` environment)
* Import Sylius Behat services in `tests/Application/config/config_test.yml` and your own Behat services as well:
    ```yaml
    imports:
        - { resource: "../../../../vendor/sylius/sylius/src/Sylius/Behat/Resources/config/services.xml" }
    ```
* Specify test application's kernel path in `behat.yml`:
    ```yaml
     FriendsOfBehat\SymfonyExtension:
        kernel:
          class: AppKernel
          path: tests/Application/app/AppKernel.php
    ```


# UPGRADE FROM `v1.2.X` TO `v1.3.0`

## Application

* Run `composer require sylius/sylius:~1.3.0 --no-update`

* Add the following code in your `behat.yml(.dist)` file:

    ```yaml
    default:
        extensions:
            FriendsOfBehat\SymfonyExtension:
                env_file: ~  
    ```
    
* Incorporate changes from the following files into plugin's test application:

    * [`tests/Application/package.json`](https://github.com/Sylius/PluginSkeleton/blob/1.3/tests/Application/package.json) ([see diff](https://github.com/Sylius/PluginSkeleton/pull/134/files#diff-726e1353c14df7d91379c0dea6b30eef)) 
    * [`tests/Application/.babelrc`](https://github.com/Sylius/PluginSkeleton/blob/1.3/tests/Application/.babelrc) ([see diff](https://github.com/Sylius/PluginSkeleton/pull/134/files#diff-a2527d9d8ad55460b2272274762c9386))
    * [`tests/Application/.eslintrc.js`](https://github.com/Sylius/PluginSkeleton/blob/1.3/tests/Application/.eslintrc.js) ([see diff](https://github.com/Sylius/PluginSkeleton/pull/134/files#diff-396c8c412b119deaa7dd84ae28ae04ca))
     
* Update PHP and JS dependencies by running `composer update` and `(cd tests/Application && yarn upgrade)`

* Clear cache by running `(cd tests/Application && bin/console cache:clear)`

* Install assets by `(cd tests/Application && bin/console assets:install web)` and `(cd tests/Application && yarn build)`

* optionally, remove the build for PHP 7.1. in `.travis.yml`
