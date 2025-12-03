<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusImojePlugin\Action;

use BitBag\SyliusImojePlugin\Api\ImojeApi;
use BitBag\SyliusImojePlugin\Resolver\SignatureResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Notify;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Factory\PaymentFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class NotifyAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    private ?Request $request;

    public function __construct(
        private RequestStack $requestStack,
        private SignatureResolverInterface $signatureResolver,
        private PaymentFactoryInterface $paymentFactory,
        private EntityManagerInterface $entityManager,
        private OrderRepositoryInterface $orderRepository,
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->apiClass = ImojeApi::class;
    }

    /** @param Notify $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if (null == $this->request) {
            throw new \Exception('Request is empty');
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        /** @var string $content */
        $content = $this->request->getContent();
        $notificationData = json_decode($content, true);
        if ($notificationData['payment']['status'] === 'pending') {
            return;
        }

        $transactionData = $notificationData['transaction'];

        $model = $request->getModel();
        $model['statusImoje'] = $transactionData['status'];

        if ($notificationData['payment']['status'] === 'error') {
            /** @var PaymentInterface $newPayment */
            $newPayment = $this->paymentFactory->createNew();
            $newPayment->setState('new');
            $newPayment->setCurrencyCode($payment->getCurrencyCode());
            $newPayment->setAmount($payment->getAmount());
            $this->entityManager->persist($newPayment);

            $order = $payment->getOrder();
            $reloadedOrder = $this->orderRepository->findOneBy(['id' => $order->getId()]);

            $reloadedOrder->getLastPayment()->setState('failed');
            $reloadedOrder->addPayment($newPayment);

            $this->entityManager->flush();
        }

        $request->setModel($model);
    }

    public function supports($request): bool
    {
        if (null == $this->request) {
            return false;
        }

        return
            $request instanceof Notify &&
            $request->getModel() instanceof ArrayObject &&
            $this->signatureResolver->verifySignature($this->request, $this->api->getServiceKey());
    }
}
