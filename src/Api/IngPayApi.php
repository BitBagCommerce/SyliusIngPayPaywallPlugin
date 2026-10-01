<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Api;

class IngPayApi implements IngPayApiInterface
{
    public function __construct(
        private string $environment,
        private string $merchantId,
        private string $serviceId,
        private string $serviceKey,
        private string $authorizationToken,
    ) {
    }

    public function getApiUrl(): string
    {
        return $this->environment === 'production' ? self::PRODUCTION_PAYWALL_URL : self::SANDBOX_PAYWALL_URL;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    public function getServiceKey(): string
    {
        return $this->serviceKey;
    }

    public function getAuthorizationToken(): string
    {
        return $this->authorizationToken;
    }
}
