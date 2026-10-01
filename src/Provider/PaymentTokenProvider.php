<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Provider;

use Ramsey\Collection\Collection;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

final class PaymentTokenProvider implements PaymentTokenProviderInterface
{
    public function __construct(
        private RepositoryInterface $orderRepository,
        private RepositoryInterface $paymentTokenRepository,
    ) {
    }

    public function provideToken(Request $request): ?PaymentSecurityTokenInterface
    {
        /** @var string $content */
        $content = $request->getContent();
        $content = json_decode($content, true);

        $transactionData = $content['transaction'];

        /** @var OrderInterface $order */
        $order = $this->getOrder($transactionData);

        /** @var Collection $payments */
        $payments = $order->getPayments();

        foreach ($payments as $payment) {
            $model = $payment->getDetails();

            $tokenHash = $model['tokenHash'] ?? null;

            if (
                null !== $tokenHash &&
                $payment->getState() !== PaymentInterface::STATE_CANCELLED &&
                $payment->getState() !== PaymentInterface::STATE_FAILED
            ) {
                return $this->getToken($tokenHash);
            }
        }

        return null;
    }

    private function getOrder(array $transactionData): ?OrderInterface
    {
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['number' => $transactionData['orderId']]);

        return $order;
    }

    private function getToken(string $hash): ?PaymentSecurityTokenInterface
    {
        /** @var PaymentSecurityTokenInterface|null $token */
        $token = $this->paymentTokenRepository->findOneBy(['hash' => $hash]);

        return $token;
    }
}
