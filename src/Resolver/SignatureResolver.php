<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusIngPayPlugin\Resolver;

use BitBag\SyliusIngPayPlugin\Enum\IngPayEnvironment;
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

            return '' !== $key ? "$key=$value" : $value;
        }, array_keys($fields), $fields);

        $dataString = implode('&', $data);

        return hash(IngPayEnvironment::HASHING_ALGORITHM->value, $dataString . $serviceKey) . ';' . IngPayEnvironment::HASHING_ALGORITHM->value;
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
