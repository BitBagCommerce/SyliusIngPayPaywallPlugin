<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin;

use BitBag\SyliusIngPayPlugin\Api\IngPayApi;
use BitBag\SyliusIngPayPlugin\Api\IngPayApiInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class IngPayGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults(
            [
                'payum.factory_name' => 'ing_pay_paywall',
                'payum.factory_title' => 'ING Pay',
            ],
        );

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'environment' => IngPayApiInterface::SANDBOX_ENVIRONMENT,
                'merchant_id' => '',
                'service_id' => '',
                'service_key' => '',
                'authorization_token' => '',
            ];
            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = ['environment', 'merchant_id', 'service_id', 'service_key', 'authorization_token'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new IngPayApi(
                    $config['environment'],
                    $config['merchant_id'],
                    $config['service_id'],
                    $config['service_key'],
                    $config['authorization_token'],
                );
            };
        }
    }
}
