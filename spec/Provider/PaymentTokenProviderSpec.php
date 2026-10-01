<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace spec\BitBag\SyliusIngPayPlugin\Provider;

use BitBag\SyliusIngPayPlugin\Provider\PaymentTokenProvider;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

final class PaymentTokenProviderSpec extends ObjectBehavior
{
    public function let(
        RepositoryInterface $orderRepository,
        RepositoryInterface $paymentTokenRepository,
    ): void {
        $this->beConstructedWith($orderRepository, $paymentTokenRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PaymentTokenProvider::class);
    }

    public function it_should_return_token_correctly(
        Request $request,
        OrderInterface $order,
        PaymentSecurityTokenInterface $token,
        PaymentInterface $payment,
        RepositoryInterface $orderRepository,
        RepositoryInterface $paymentTokenRepository,
    ): void {
        $orderNumber = 500;
        $tokenHash = '3423423453fsxzc';

        $transactionData = [
            'transaction' => [
                'orderId' => $orderNumber,
                'tokenHash' => $tokenHash,
            ],
        ];

        $request->getContent()->willReturn(json_encode($transactionData));
        $orderRepository->findOneBy(['number' => $orderNumber])->willReturn($order);
        $order->getPayments()
            ->willReturn(new ArrayCollection([
                $payment->getWrappedObject(),
            ]));
        $payment->getState()->willReturn(PaymentInterface::STATE_NEW);
        $payment->getDetails()->willReturn(['tokenHash' => $tokenHash]);
        $paymentTokenRepository->findOneBy(['hash' => $tokenHash])->willReturn($token);

        $this->provideToken($request)->shouldReturn($token);
    }

    public function it_should_return_null_if_paymentTokenRepository_is_not_called(
        Request $request,
        OrderInterface $order,
        PaymentInterface $payment,
        RepositoryInterface $orderRepository,
    ): void {
        $orderNumber = 500;
        $tokenHash = '3423423453fsxzc';
        $transactionData = [
            'transaction' => [
                'orderId' => $orderNumber,
                'tokenHash' => $tokenHash,
            ],
        ];
        $request->getContent()->willReturn(json_encode($transactionData));
        $orderRepository->findOneBy(['number' => $orderNumber])
            ->willReturn($order);
        $order->getPayments()
            ->willReturn(new ArrayCollection([
                $payment->getWrappedObject(),
            ]));
        $payment->getState()->willReturn(PaymentInterface::STATE_CANCELLED);
        $payment->getDetails()->willReturn(['tokenHash' => $tokenHash]);

        $this->provideToken($request)->shouldReturn(null);
    }
}
