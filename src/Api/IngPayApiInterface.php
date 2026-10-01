<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Api;

interface IngPayApiInterface
{
    public const SANDBOX_ENVIRONMENT = 'sandbox';

    public const PRODUCTION_ENVIRONMENT = 'production';

    public const SANDBOX_PAYWALL_URL = 'https://sandbox.paywall.imoje.pl/payment';

    public const PRODUCTION_PAYWALL_URL = 'https://paywall.imoje.pl/payment';

    public const NEW_STATUS = 'new';

    public const PENDING_STATUS = 'pending';

    public const SETTLED_STATUS = 'settled';

    public const REJECTED_STATUS = 'rejected';

    public const CANCELLED_STATUS = 'cancelled';

    public const HASHING_ALGORITHM = 'sha256';

    public function getApiUrl(): string;

    public function getMerchantId(): string;

    public function getServiceId(): string;

    public function getServiceKey(): string;

    public function getAuthorizationToken(): string;
}
