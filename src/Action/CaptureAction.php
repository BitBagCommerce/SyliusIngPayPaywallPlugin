<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Action;

use BitBag\SyliusIngPayPlugin\Api\IngPayApi;
use BitBag\SyliusIngPayPlugin\Api\IngPayApiInterface;
use BitBag\SyliusIngPayPlugin\Resolver\SignatureResolverInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct(private SignatureResolverInterface $signatureResolver)
    {
        $this->apiClass = IngPayApi::class;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = $request->getModel();

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        /** @var PaymentSecurityTokenInterface $token */
        $token = $request->getToken();

        $orderData = $this->prepareOrderData($order, $token);

        $model['tokenHash'] = $token->getHash();
        $model['statusIngPay'] = IngPayApiInterface::NEW_STATUS;
        $model['paymentId'] = $payment->getId();
        $request->setModel($model);

        throw new HttpPostRedirect(
            $this->api->getApiUrl(),
            $orderData,
        );
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof ArrayObject;
    }

    private function prepareOrderData(OrderInterface $order, PaymentSecurityTokenInterface $token): array
    {
        $orderData = [];

        $billingAddress = $order->getBillingAddress();
        $customer = $order->getCustomer();

        $orderData['serviceId'] = $this->api->getServiceId();
        $orderData['merchantId'] = $this->api->getMerchantId();
        $orderData['amount'] = $order->getTotal();
        $orderData['currency'] = $order->getCurrencyCode();
        $orderData['orderId'] = $order->getNumber();
        $orderData['customerFirstName'] = $billingAddress?->getFirstName();
        $orderData['customerLastName'] = $billingAddress?->getLastName();
        $orderData['urlReturn'] = $token->getAfterUrl();
        $orderData['customerEmail'] = $customer?->getEmail();
        $orderData['signature'] = $this->signatureResolver->createSignature($orderData, $this->api->getServiceKey());

        return $orderData;
    }
}
