<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Action;

use ArrayAccess;
use BitBag\SyliusIngPayPlugin\Api\IngPayApiInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

final class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = $request->getModel();
        $status = $model['statusIngPay'] ?? null;
        $paymentId = $model['paymentId'] ?? null;

        if (($status === null || IngPayApiInterface::NEW_STATUS === $status) && null !== $paymentId) {
            $request->markNew();

            return;
        }

        if (IngPayApiInterface::PENDING_STATUS === $status) {
            $request->markPending();

            return;
        }

        if (IngPayApiInterface::CANCELLED_STATUS === $status) {
            $request->markCanceled();

            $model['tokenHash'] = '';
            $request->setModel($model);

            return;
        }

        if (IngPayApiInterface::REJECTED_STATUS === $status) {
            $request->markFailed();

            $model['tokenHash'] = '';
            $request->setModel($model);

            return;
        }

        if (IngPayApiInterface::SETTLED_STATUS === $status) {
            $request->markCaptured();

            return;
        }

        $request->markUnknown();
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
