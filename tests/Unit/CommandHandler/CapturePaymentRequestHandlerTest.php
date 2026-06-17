<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusIngPayPlugin\Unit\CommandHandler;

use BitBag\SyliusIngPayPlugin\Command\CapturePaymentRequest;
use BitBag\SyliusIngPayPlugin\CommandHandler\CapturePaymentRequestHandler;
use BitBag\SyliusIngPayPlugin\Enum\IngPayEnvironment;
use BitBag\SyliusIngPayPlugin\Resolver\SignatureResolverInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\CoreBundle\OrderPay\Provider\UrlProviderInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CapturePaymentRequestHandlerTest extends TestCase
{
    private PaymentRequestProviderInterface $paymentRequestProvider;
    private StateMachineInterface $stateMachine;
    private SignatureResolverInterface $signatureResolver;
    private UrlProviderInterface $afterPayUrlProvider;
    private CapturePaymentRequestHandler $handler;

    protected function setUp(): void
    {
        $this->paymentRequestProvider = $this->createMock(PaymentRequestProviderInterface::class);
        $this->stateMachine = $this->createMock(StateMachineInterface::class);
        $this->signatureResolver = $this->createMock(SignatureResolverInterface::class);
        $this->afterPayUrlProvider = $this->createMock(UrlProviderInterface::class);

        $this->handler = new CapturePaymentRequestHandler(
            $this->paymentRequestProvider,
            $this->stateMachine,
            $this->signatureResolver,
            $this->afterPayUrlProvider
        );
    }

    public function testItProcessesCapturePaymentRequest(): void
    {
        $capturePaymentRequest = new CapturePaymentRequest('payment-request-id');

        $paymentRequest = $this->createMock(PaymentRequestInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $billingAddress = $this->createMock(AddressInterface::class);
        $customer = $this->createMock(CustomerInterface::class);

        $paymentRequest->method('getMethod')->willReturn($paymentMethod);
        $paymentRequest->method('getPayment')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);

        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getConfig')->willReturn([
            'environment' => IngPayEnvironment::SANDBOX_ENVIRONMENT,
            'service_key' => 'test_key',
            'service_id' => 'test_service',
            'merchant_id' => 'test_merchant',
        ]);

        $order->method('getBillingAddress')->willReturn($billingAddress);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getTotal')->willReturn(1000);
        $order->method('getCurrencyCode')->willReturn('USD');
        $order->method('getNumber')->willReturn('ORDER123');

        $this->afterPayUrlProvider->method('getUrl')
            ->with($paymentRequest, UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://return.url');

        $this->signatureResolver->method('createSignature')
            ->with([
                'serviceId' => 'test_service',
                'merchantId' => 'test_merchant',
                'amount' => 1000,
                'currency' => 'USD',
                'orderId' => 'ORDER123',
                'customerFirstName' => null,
                'customerLastName' => null,
                'urlReturn' => 'https://return.url',
                'customerEmail' => null,
            ], 'test_key')
            ->willReturn('test_signature');

        $paymentRequest->expects(self::once())
            ->method('setResponseData')
            ->with([
                'url' => IngPayEnvironment::SANDBOX_URL->value,
                'orderData' => [
                    'serviceId' => 'test_service',
                    'merchantId' => 'test_merchant',
                    'amount' => 1000,
                    'currency' => 'USD',
                    'orderId' => 'ORDER123',
                    'customerFirstName' => null,
                    'customerLastName' => null,
                    'urlReturn' => 'https://return.url',
                    'customerEmail' => null,
                    'signature' => 'test_signature',
                ],
            ]);

        $this->stateMachine->expects(self::once())
            ->method('apply')
            ->with($paymentRequest, PaymentRequestTransitions::GRAPH, PaymentRequestTransitions::TRANSITION_PROCESS);

        $this->paymentRequestProvider->method('provide')
            ->with($capturePaymentRequest)
            ->willReturn($paymentRequest);

        ($this->handler)($capturePaymentRequest);

        $this->addToAssertionCount(1);
    }
}
