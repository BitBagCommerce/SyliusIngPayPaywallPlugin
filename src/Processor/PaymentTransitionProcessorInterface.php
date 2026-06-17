<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Processor;

use Sylius\Component\Payment\Model\PaymentRequestInterface;

interface PaymentTransitionProcessorInterface
{
    public const STATE_PENDING = 'pending';

    public const STATE_SETTLED = 'settled';

    public const STATE_CANCELLED = 'cancelled';

    public const STATE_REJECTED = 'rejected';

    public function process(PaymentRequestInterface $paymentRequest): void;
}
