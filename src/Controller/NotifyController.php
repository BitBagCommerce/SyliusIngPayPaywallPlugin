<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Controller;

use BitBag\SyliusIngPayPlugin\Resolver\SignatureResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final readonly class NotifyController
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private PaymentRepositoryInterface $paymentRepository,
        private EntityManagerInterface $entityManager,
        private SignatureResolverInterface $signatureResolver,
    ) {
    }

    public function verifyIngPayNotification(Request $request): Response
    {
        $content = $request->getContent();
        if ('' === $content) {
            return new Response('There is no content in request.', Response::HTTP_NO_CONTENT);
        }

        $data = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            return new Response('Invalid JSON structure', Response::HTTP_BAD_REQUEST);
        }

        $orderNumber = (string) $data['payment']['orderId'];
        if ('' === $orderNumber) {
            return new Response('There is no order number in request data.', Response::HTTP_NO_CONTENT);
        }

        $order = $this->orderRepository->findOneByNumber($orderNumber);
        Assert::notNull($order, sprintf(
            'There is no order for number: %s.',
            $orderNumber,
        ));

        $orderId = (string) $order->getId();
        /** @var PaymentInterface|null $payment */
        $payment = $this->paymentRepository->findOneBy(['order' => $orderId]);
        Assert::notNull($payment, sprintf(
            'There is no payment registered for order: %s.',
            $orderId,
        ));

        $paymentMethod = $payment->getMethod();
        Assert::notNull($paymentMethod, sprintf(
            'There is no payment method in payment: %s.',
            $payment->getId(),
        ));

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig, sprintf(
            'The payment method (code: %s) has not been configured.',
            $paymentMethod->getCode(),
        ));

        /** @var string $serviceKey */
        $serviceKey = $gatewayConfig->getConfig()['service_key'];

        if (false === $this->signatureResolver->verifySignature($request, $serviceKey)) {
            return new Response('Signature verification failed', Response::HTTP_FORBIDDEN);
        }

        $ingPayPaymentStatus = (string) $data['payment']['status'];
        $payment->setDetails(['status' => $ingPayPaymentStatus]);
        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return new JsonResponse([
            'status' => $ingPayPaymentStatus,
        ]);
    }
}
