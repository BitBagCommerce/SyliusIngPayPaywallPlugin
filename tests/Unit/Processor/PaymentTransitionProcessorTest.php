<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusIngPayPlugin\Unit\Processor;

use BitBag\SyliusIngPayPlugin\Processor\PaymentTransitionProcessor;
use BitBag\SyliusIngPayPlugin\Processor\PaymentTransitionProcessorInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Abstraction\StateMachine\StateMachineInterface;

final class PaymentTransitionProcessorTest extends TestCase
{
    private StateMachineInterface $stateMachine;
    private PaymentTransitionProcessor $processor;

    protected function setUp(): void
    {
        $this->stateMachine = $this->createMock(StateMachineInterface::class);
        $this->processor = new PaymentTransitionProcessor($this->stateMachine);
    }

    /**
     * @dataProvider paymentStatusProvider
     */
    public function testItProcessesPaymentWithDifferentStatuses(
        string $status,
        ?string $expectedTransition
    ): void {
        $paymentRequest = $this->createMock(PaymentRequestInterface::class);
        $payment = $this->createMock(PaymentInterface::class);

        $payment->method('getDetails')->willReturn(['status' => $status]);
        $paymentRequest->method('getPayment')->willReturn($payment);

        if ($expectedTransition) {
            $this->stateMachine
                ->expects(self::once())
                ->method('can')
                ->with($payment, PaymentTransitions::GRAPH, $expectedTransition)
                ->willReturn(true);

            $this->stateMachine
                ->expects(self::once())
                ->method('apply')
                ->with($payment, PaymentTransitions::GRAPH, $expectedTransition);
        } else {
            $this->stateMachine
                ->expects(self::never())
                ->method('apply');
        }

        $this->processor->process($paymentRequest);

        $this->addToAssertionCount(1);
    }

    public function testItDoesNotApplyTransitionIfCannotTransition(): void
    {
        $paymentRequest = $this->createMock(PaymentRequestInterface::class);
        $payment = $this->createMock(PaymentInterface::class);

        $payment->method('getDetails')->willReturn(['status' => PaymentTransitionProcessorInterface::STATE_PENDING]);
        $paymentRequest->method('getPayment')->willReturn($payment);

        $this->stateMachine
            ->expects(self::once())
            ->method('can')
            ->with($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_PROCESS)
            ->willReturn(false);

        $this->stateMachine
            ->expects(self::never())
            ->method('apply');

        $this->processor->process($paymentRequest);

        $this->addToAssertionCount(1);
    }

    public static function paymentStatusProvider(): array
    {
        return [
            'Pending status' => [
                PaymentTransitionProcessorInterface::STATE_PENDING,
                PaymentTransitions::TRANSITION_PROCESS,
            ],
            'Settled status' => [
                PaymentTransitionProcessorInterface::STATE_SETTLED,
                PaymentTransitions::TRANSITION_COMPLETE,
            ],
            'Cancelled status' => [
                PaymentTransitionProcessorInterface::STATE_CANCELLED,
                PaymentTransitions::TRANSITION_CANCEL,
            ],
            'Rejected status' => [
                PaymentTransitionProcessorInterface::STATE_REJECTED,
                PaymentTransitions::TRANSITION_FAIL,
            ],
            'Unknown status (no transition)' => [
                'unknown_status',
                null,
            ],
        ];
    }
}
