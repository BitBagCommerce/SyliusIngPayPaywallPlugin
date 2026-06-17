<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Processor;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\PaymentTransitions;

final readonly class PaymentTransitionProcessor implements PaymentTransitionProcessorInterface
{
    public function __construct(
        private StateMachineInterface $stateMachine,
    ) {
    }

    public function process(PaymentRequestInterface $paymentRequest): void
    {
        $payment = $paymentRequest->getPayment();

        $transition = $this->getTransition($payment);

        if (null === $transition) {
            return;
        }

        if ($this->stateMachine->can($payment, PaymentTransitions::GRAPH, $transition)) {
            $this->stateMachine->apply($payment, PaymentTransitions::GRAPH, $transition);
        }
    }

    private function getTransition(PaymentInterface $payment): ?string
    {
        $details = $payment->getDetails();
        /** @var ?string $status */
        $status = $details['status'] ?? null;

        return match ($status) {
            PaymentTransitionProcessorInterface::STATE_PENDING => PaymentTransitions::TRANSITION_PROCESS,
            PaymentTransitionProcessorInterface::STATE_SETTLED => PaymentTransitions::TRANSITION_COMPLETE,
            PaymentTransitionProcessorInterface::STATE_CANCELLED => PaymentTransitions::TRANSITION_CANCEL,
            PaymentTransitionProcessorInterface::STATE_REJECTED => PaymentTransitions::TRANSITION_FAIL,
            default => null
        };
    }
}
