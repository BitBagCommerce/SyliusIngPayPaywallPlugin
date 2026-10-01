<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Controller;

use BitBag\SyliusIngPayPlugin\Provider\PaymentTokenProviderInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Notify;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NotifyController
{
    public function __construct(
        private Payum $payum,
        private PaymentTokenProviderInterface $paymentTokenProvider,
    ) {
    }

    public function verifyIngPayNotification(Request $request): Response
    {
        if ('' === $request->getContent()) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $paymentToken = $this->paymentTokenProvider->provideToken($request);

        if (null === $paymentToken) {
            throw new NotFoundHttpException('Payment token not found');
        }

        $notifyToken = $this->payum->getHttpRequestVerifier()->verify($this->createRequestWithToken($request, $paymentToken));
        $gateway = $this->payum->getGateway($notifyToken->getGatewayName());

        $gateway->execute(new Notify($notifyToken));

        return new JsonResponse(['status' => 'ok']);
    }

    private function createRequestWithToken(
        Request $request,
        PaymentSecurityTokenInterface $token,
    ): Request {
        $request = Request::create(
            $token->getTargetUrl(),
            $request->getMethod(),
            $request->query->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $request->getContent(),
        );

        $request->attributes->add([
            'payum_token' => $token->getHash(),
        ]);

        return $request;
    }
}
