<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Enum;

enum IngPayEnvironment: string
{
    case PRODUCTION_ENVIRONMENT = 'production';
    case SANDBOX_ENVIRONMENT = 'sandbox';
    case PRODUCTION_URL = 'https://paywall.imoje.pl/payment';
    case SANDBOX_URL = 'https://sandbox.paywall.imoje.pl/payment';
    case HASHING_ALGORITHM = 'sha256';
}
