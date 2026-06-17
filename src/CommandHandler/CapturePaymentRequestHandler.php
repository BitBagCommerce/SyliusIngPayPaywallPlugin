<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\CommandHandler;

use BitBag\SyliusIngPayPlugin\Command\CapturePaymentRequest;
use BitBag\SyliusIngPayPlugin\Enum\IngPayEnvironment;
use BitBag\SyliusIngPayPlugin\Resolver\SignatureResolverInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\CoreBundle\OrderPay\Provider\UrlProviderInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final readonly class CapturePaymentRequestHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
        private SignatureResolverInterface $signatureResolver,
        private UrlProviderInterface $afterPayUrlProvider,
    ) {
    }

    public function __invoke(CapturePaymentRequest $capturePaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($capturePaymentRequest);

        $paymentMethod = $paymentRequest->getMethod();
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig, sprintf(
            'The payment method (code: %s) has not been configured.',
            $paymentMethod->getCode(),
        ));
        /** @var PaymentInterface $payment */
        $payment = $paymentRequest->getPayment();
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        /** @var string $environment */
        $environment = $gatewayConfig->getConfig()['environment']->value;
        $returnUrl = $this->afterPayUrlProvider->getUrl($paymentRequest, UrlGeneratorInterface::ABSOLUTE_URL);

        $paymentRequest->setResponseData([
            'url' => $this->getPaymentUrl($environment),
            'orderData' => $this->prepareOrderData($order, $gatewayConfig, $returnUrl),
        ]);

        $this->stateMachine->apply(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_PROCESS,
        );
    }

    private function prepareOrderData(
        OrderInterface $order,
        GatewayConfigInterface $gatewayConfig,
        string $returnUrl,
    ): array {
        $orderData = [];

        $billingAddress = $order->getBillingAddress();
        $customer = $order->getCustomer();
        /** @var string $serviceKey */
        $serviceKey = $gatewayConfig->getConfig()['service_key'];
        /** @var string $serviceId */
        $serviceId = $gatewayConfig->getConfig()['service_id'];
        /** @var string $merchantId */
        $merchantId = $gatewayConfig->getConfig()['merchant_id'];

        $orderData['serviceId'] = $serviceId;
        $orderData['merchantId'] = $merchantId;
        $orderData['amount'] = $order->getTotal();
        $orderData['currency'] = $order->getCurrencyCode();
        $orderData['orderId'] = $order->getNumber();
        $orderData['customerFirstName'] = $billingAddress?->getFirstName();
        $orderData['customerLastName'] = $billingAddress?->getLastName();
        $orderData['urlReturn'] = $returnUrl;
        $orderData['customerEmail'] = $customer?->getEmail();
        $orderData['signature'] = $this->signatureResolver->createSignature($orderData, $serviceKey);

        return $orderData;
    }

    private function getPaymentUrl(string $environment): string
    {
        return $environment === IngPayEnvironment::PRODUCTION_ENVIRONMENT->value ? IngPayEnvironment::PRODUCTION_URL->value : IngPayEnvironment::SANDBOX_URL->value;
    }
}
