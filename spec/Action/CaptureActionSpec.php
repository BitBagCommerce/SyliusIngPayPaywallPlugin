<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace spec\BitBag\SyliusIngPayPlugin\Action;

use BitBag\SyliusIngPayPlugin\Api\IngPayApi;
use BitBag\SyliusIngPayPlugin\Api\IngPayApiInterface;
use BitBag\SyliusIngPayPlugin\Resolver\SignatureResolverInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\HttpFoundation\Request;

final class CaptureActionSpec extends ObjectBehavior
{
    public function let(
        SignatureResolverInterface $signatureResolver,
    ): void {
        $this->beConstructedWith($signatureResolver);
    }

    public function it_should_return_true_when_request_and_model_is_valid(
        Capture $request,
        ArrayObject $arrayObject,
    ): void {
        $request->getModel()->willReturn($arrayObject);

        $this->supports($request)->shouldReturn(true);
    }

    public function it_should_return_false_when_model_is_invalid(
        Capture $request,
    ): void {
        $request->getModel()->willReturn(null);

        $this->supports($request)->shouldReturn(false);
    }

    public function it_should_return_false_when_request_is_not_a_capture_instance(
        Request $request,
    ): void {
        $this->supports($request)->shouldReturn(false);
    }

    public function it_executes_correctly_with_valid_request(
        Capture $request,
        OrderInterface $order,
        PaymentInterface $payment,
        PaymentSecurityTokenInterface $token,
        AddressInterface $address,
        CustomerInterface $customer,
        IngPayApi $apiClass,
        SignatureResolverInterface $signatureResolver,
    ): void {
        $data = ['statusIngPay' => IngPayApiInterface::NEW_STATUS, 'paymentId' => 123, 'tokenHash' => '1234sdcsdfxz'];
        $request->getModel()->willReturn(new ArrayObject($data));

        $request->getToken()->willReturn('1234sdcsdfxz');
        $request->getFirstModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $payment->getOrder()->willReturn($order);
        $payment->getId()->willReturn(123);

        $token->getHash()->willReturn('1234sdcsdfxz');

        $apiClass->getServiceKey()->willReturn('1234sdcsdfxz');
        $order->getBillingAddress()->willReturn($address);
        $order->getCustomer()->willReturn($customer);

        $apiClass->getApiUrl()->willReturn('http://example.com/');

        $apiClass->getServiceId()->willReturn('1');
        $apiClass->getMerchantId()->willReturn('1');
        $order->getTotal()->willReturn(1000);
        $order->getCurrencyCode()->willReturn('EUR');
        $order->getNumber()->willReturn('123');
        $address->getFirstName()->willReturn('John Doe');
        $address->getLastName()->willReturn('Smith');
        $token->getAfterUrl()->willReturn('http://example.com/');
        $customer->getEmail()->willReturn('john@doe.com');

        $orderData = [
            'serviceId' => '1',
            'merchantId' => '1',
            'amount' => 1000,
            'currency' => 'EUR',
            'orderId' => '123',
            'customerFirstName' => 'John Doe',
            'customerLastName' => 'Smith',
            'urlReturn' => 'http://example.com/',
            'customerEmail' => 'john@doe.com',
        ];

        $signatureResolver->createSignature($orderData, '1234sdcsdfxz')
              ->willReturn('signature');

        $request->setModel(new ArrayObject($data))->shouldBeCalled();

        $this->setApi($apiClass);
        $this->shouldThrow(HttpPostRedirect::class)
            ->during('execute', [$request]);
    }
}
