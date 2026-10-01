<?php

declare(strict_types=1);

namespace spec\BitBag\SyliusIngPayPlugin\Resolver;

use BitBag\SyliusIngPayPlugin\Api\IngPayApiInterface;
use BitBag\SyliusIngPayPlugin\Resolver\SignatureResolver;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;

final class SignatureResolverSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(SignatureResolver::class);
    }

    public function it_should_sort_fields_and_build_data_string(): void
    {
        $fields = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];
        $serviceKey = 'adasvcx3412';
        $expectedDataString = 'field1=value1&field2=value2';
        $expectedHash = hash(IngPayApiInterface::HASHING_ALGORITHM, $expectedDataString . $serviceKey) . ';' . IngPayApiInterface::HASHING_ALGORITHM;

        $this->createSignature($fields, $serviceKey)->shouldReturn($expectedHash);
    }

    public function it_should_return_hash_with_service_key_only_when_fields_are_empty(): void
    {
        $fields = [];
        $serviceKey = 'adasvcx3412';
        $expectedHash = hash(IngPayApiInterface::HASHING_ALGORITHM, $serviceKey) . ';' . IngPayApiInterface::HASHING_ALGORITHM;

        $this->createSignature($fields, $serviceKey)->shouldReturn($expectedHash);
    }

    public function it_should_return_hash_without_service_key_when_service_key_is_empty(): void
    {
        $fields = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];
        $serviceKey = '';
        $expectedDataString = 'field1=value1&field2=value2';
        $expectedHash = hash(IngPayApiInterface::HASHING_ALGORITHM, $expectedDataString) . ';' . IngPayApiInterface::HASHING_ALGORITHM;

        $this->createSignature($fields, $serviceKey)->shouldReturn($expectedHash);
    }

    public function it_should_return_true_if_signatures_match(
        Request $request,
    ): void {
        $serviceKey = 'adasvcx3412';
        $body = 'test.jpg';
        $exampleHash = hash('sha256', sprintf('test.jpg%s', $serviceKey));
        $headerSignature = sprintf('alg=sha256;signature=%s', $exampleHash);
        $request->getContent()->willReturn($body);

        $request->headers = new \Symfony\Component\HttpFoundation\HeaderBag(['X-Imoje-Signature' => $headerSignature]);

        $this->verifySignature($request, $serviceKey)->shouldBe(true);
    }

    public function it_should_return_false_if_signatures_not_match(
        Request $request,
    ): void {
        $serviceKey = 'adasvcx3412';
        $body = 'test2.jpg';
        $exampleHash = hash('sha256', sprintf('test.jpg%s', $serviceKey));
        $headerSignature = sprintf('alg=sha256;signature=%s', $exampleHash);
        $request->getContent()->willReturn($body);

        $request->headers = new \Symfony\Component\HttpFoundation\HeaderBag(['X-Imoje-Signature' => $headerSignature]);

        $this->verifySignature($request, $serviceKey)->shouldBe(false);
    }

    public function it_should_return_false_if_content_is_empty(
        Request $request,
    ): void {
        $serviceKey = '';
        $body = '';
        $exampleHash = '';
        $headerSignature = sprintf('alg=sha256;signature=%s', $exampleHash);
        $request->getContent()->willReturn($body);

        $request->headers = new \Symfony\Component\HttpFoundation\HeaderBag(['X-Imoje-Signature' => $headerSignature]);

        $this->verifySignature($request, $serviceKey)->shouldBe(false);
    }
}
