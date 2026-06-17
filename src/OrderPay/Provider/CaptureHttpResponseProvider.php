<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\OrderPay\Provider;

use Sylius\Bundle\PaymentBundle\Provider\HttpResponseProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class CaptureHttpResponseProvider implements HttpResponseProviderInterface
{
    public function supports(RequestConfiguration $requestConfiguration, PaymentRequestInterface $paymentRequest): bool
    {
        return PaymentRequestInterface::STATE_PROCESSING === $paymentRequest->getState();
    }

    public function getResponse(RequestConfiguration $requestConfiguration, PaymentRequestInterface $paymentRequest): Response
    {
        $data = $paymentRequest->getResponseData();
        /** @var string $url */
        $url = $data['url'];
        $params = $data['orderData'];
        $parsedParams = http_build_query($params);
        $finalUrl = sprintf('%s?%s', $url, $parsedParams);

        return new RedirectResponse($finalUrl, Response::HTTP_SEE_OTHER);
    }
}
