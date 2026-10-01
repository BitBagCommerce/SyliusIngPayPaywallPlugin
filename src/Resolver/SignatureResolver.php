<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Resolver;

use BitBag\SyliusIngPayPlugin\Api\IngPayApiInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class SignatureResolver implements SignatureResolverInterface
{
    public function createSignature(array $fields, string $serviceKey): string
    {
        ksort($fields);

        $data = array_map(function ($key, $value) {
            if (is_array($value)) {
                $value = http_build_query([$key => $value], '', '&');
                $key = '';
            }

            return $key !== '' ? "$key=$value" : $value;
        }, array_keys($fields), $fields);

        $dataString = implode('&', $data);

        return hash(IngPayApiInterface::HASHING_ALGORITHM, $dataString . $serviceKey) . ';' . IngPayApiInterface::HASHING_ALGORITHM;
    }

    public function verifySignature(Request $request, string $serviceKey): bool
    {
        /** @var string $headerSignature */
        $headerSignature = $request->headers->get('X-Imoje-Signature');
        $body = $request->getContent();

        $parts = [];
        parse_str(str_replace([';', '='], ['&', '='], $headerSignature), $parts);

        Assert::keyExists($parts, 'alg');
        Assert::string($parts['alg']);

        $ownSignature = hash($parts['alg'], $body . $serviceKey);

        return $ownSignature === $parts['signature'];
    }
}
