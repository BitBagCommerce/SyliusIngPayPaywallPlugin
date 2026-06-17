<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\CommandHandler;

use BitBag\SyliusIngPayPlugin\Command\CaptureEndPaymentRequest;
use BitBag\SyliusIngPayPlugin\Processor\PaymentTransitionProcessorInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CaptureEndPaymentRequestHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
        private PaymentTransitionProcessorInterface $paymentTransitionProcessor,
    ) {
    }

    public function __invoke(CaptureEndPaymentRequest $captureEndPaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($captureEndPaymentRequest);

        if (PaymentRequestInterface::STATE_PROCESSING !== $paymentRequest->getState()) {
            return;
        }

        $this->paymentTransitionProcessor->process($paymentRequest);

        $this->stateMachine->apply(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_COMPLETE,
        );
    }
}
