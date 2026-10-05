<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace spec\BitBag\SyliusImojePlugin\Action;

use BitBag\SyliusImojePlugin\Api\ImojeApi;
use BitBag\SyliusImojePlugin\Resolver\SignatureResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Notify;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Factory\PaymentFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class NotifyActionSpec extends ObjectBehavior
{
    public function let(
        RequestStack $requestStack,
        SignatureResolverInterface $signatureResolver,
        PaymentFactoryInterface $paymentFactory,
        EntityManagerInterface $entityManager,
        OrderRepositoryInterface $orderRepository,
    ): void {
        $this->beConstructedWith(
            $requestStack,
            $signatureResolver,
            $paymentFactory,
            $entityManager,
            $orderRepository,
        );
    }

    public function it_should_return_true_when_request_and_request_data_is_valid(
        Notify $request,
        ArrayObject $arrayObject,
        SignatureResolverInterface $signatureResolver,
        ImojeApi $api,
        Request $httpRequest,
        RequestStack $requestStack,
    ): void {
        $request->getModel()->willReturn($arrayObject);
        $requestStack->getCurrentRequest()
            ->willReturn($httpRequest);
        $api->getServiceKey()->willReturn('1234sdcsdfxz');
        $signatureResolver->verifySignature($httpRequest, '1234sdcsdfxz')
            ->willReturn(true);

        $this->setApi($api);
        $this->supports($request)->shouldReturn(true);
    }

    public function it_should_return_false_when_request_and_signature_are_invalid(
        Notify $request,
        ArrayObject $arrayObject,
        SignatureResolverInterface $signatureResolver,
        ImojeApi $api,
        Request $httpRequest,
        RequestStack $requestStack,
    ): void {
        $request->getModel()->willReturn($arrayObject);
        $requestStack->getCurrentRequest()->willReturn($httpRequest);
        $api->getServiceKey()->willReturn('1234sdcsdfxz');
        $signatureResolver->verifySignature($httpRequest, '1234sdcsdfxz')
            ->willReturn(false);

        $this->setApi($api);
        $this->supports($request)->shouldReturn(false);
    }

    public function it_should_return_false_when_request_is_empty(
        Notify $request,
        ImojeApi $api,
        RequestStack $requestStack,
        Request $httpRequest,
    ): void {
        $request->getModel()->willReturn(null);
        $requestStack->getCurrentRequest()->willReturn($httpRequest);
        $api->getServiceKey()->willReturn(null);

        $this->supports($request)->shouldReturn(false);
    }

    public function it_should_return_false_when_request_is_invalid_and(
        Notify $request,
        RequestStack $requestStack,
        Request $httpRequest,
    ): void {
        $request->getModel()->willReturn(null);
        $requestStack->getCurrentRequest()->willReturn($httpRequest);

        $this->supports($httpRequest)->shouldReturn(false);
    }

    public function it_sets_model_status_from_notification_data(
        Notify $request,
        ArrayObject $arrayObject,
        SignatureResolverInterface $signatureResolver,
        ImojeApi $api,
        Request $httpRequest,
        RequestStack $requestStack,
        PaymentInterface $payment,
    ): void {
        $requestStack->getCurrentRequest()->willReturn($httpRequest);
        $request->getFirstModel()->willReturn($payment);
        $api->getServiceKey()->willReturn('1234sdcsdfxz');
        $signatureResolver->verifySignature($httpRequest, '1234sdcsdfxz')
            ->willReturn(true);
        $notificationData = [
            'payment' => ['status' => 'new'],
            'transaction' => ['status' => 'new', 'paymentId' => 1, 'tokenHash' => '1234sdcsdfxz'],
        ];
        $jsonNotificationData = json_encode($notificationData);
        $httpRequest->getContent()->willReturn($jsonNotificationData);
        $request->getModel()->willReturn(new ArrayObject([
            'status' => 'new',
            'paymentId' => 1,
            'tokenHash' => '1234sdcsdfxz',
        ]));
        $request->setModel(new ArrayObject([
             'status' => 'new',
             'paymentId' => 1,
             'tokenHash' => '1234sdcsdfxz',
             'statusImoje' => 'new',
        ]))->willReturn($arrayObject);

        $this->setApi($api);
        $this->execute($request);
    }
}
