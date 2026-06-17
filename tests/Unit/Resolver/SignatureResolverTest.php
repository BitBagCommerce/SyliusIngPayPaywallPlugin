<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusIngPayPlugin\Unit\Resolver;

use BitBag\SyliusIngPayPlugin\Enum\IngPayEnvironment;
use BitBag\SyliusIngPayPlugin\Resolver\SignatureResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

final class SignatureResolverTest extends TestCase
{
    private SignatureResolver $signatureResolver;

    protected function setUp(): void
    {
        $this->signatureResolver = new SignatureResolver();
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(SignatureResolver::class, $this->signatureResolver);
    }

    public function testItShouldSortFieldsAndBuildDataString(): void
    {
        $fields = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];
        $serviceKey = 'adasvcx3412';
        $expectedDataString = 'field1=value1&field2=value2';
        $expectedHash = hash(IngPayEnvironment::HASHING_ALGORITHM->value, $expectedDataString . $serviceKey) . ';' . IngPayEnvironment::HASHING_ALGORITHM->value;

        $this->assertSame($expectedHash, $this->signatureResolver->createSignature($fields, $serviceKey));
    }

    public function testItShouldReturnHashWithServiceKeyOnlyWhenFieldsAreEmpty(): void
    {
        $fields = [];
        $serviceKey = 'adasvcx3412';
        $expectedHash = hash(IngPayEnvironment::HASHING_ALGORITHM->value, $serviceKey) . ';' . IngPayEnvironment::HASHING_ALGORITHM->value;

        $this->assertSame($expectedHash, $this->signatureResolver->createSignature($fields, $serviceKey));
    }

    public function testItShouldReturnHashWithoutServiceKeyWhenServiceKeyIsEmpty(): void
    {
        $fields = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];
        $serviceKey = '';
        $expectedDataString = 'field1=value1&field2=value2';
        $expectedHash = hash(IngPayEnvironment::HASHING_ALGORITHM->value, $expectedDataString) . ';' . IngPayEnvironment::HASHING_ALGORITHM->value;

        $this->assertSame($expectedHash, $this->signatureResolver->createSignature($fields, $serviceKey));
    }

    public function testItShouldReturnTrueIfSignaturesMatch(): void
    {
        $serviceKey = 'adasvcx3412';
        $body = 'test.jpg';
        $exampleHash = hash('sha256', sprintf('test.jpg%s', $serviceKey));
        $headerSignature = sprintf('alg=sha256;signature=%s', $exampleHash);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn($body);
        $request->headers = new HeaderBag(['X-Imoje-Signature' => $headerSignature]);

        $this->assertTrue($this->signatureResolver->verifySignature($request, $serviceKey));
    }

    public function testItShouldReturnFalseIfSignaturesDoNotMatch(): void
    {
        $serviceKey = 'adasvcx3412';
        $body = 'test2.jpg';
        $exampleHash = hash('sha256', sprintf('test.jpg%s', $serviceKey));
        $headerSignature = sprintf('alg=sha256;signature=%s', $exampleHash);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn($body);
        $request->headers = new HeaderBag(['X-Imoje-Signature' => $headerSignature]);

        $this->assertFalse($this->signatureResolver->verifySignature($request, $serviceKey));
    }

    public function testItShouldReturnFalseIfContentIsEmpty(): void
    {
        $serviceKey = '';
        $body = '';
        $exampleHash = '';
        $headerSignature = sprintf('alg=sha256;signature=%s', $exampleHash);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn($body);
        $request->headers = new HeaderBag(['X-Imoje-Signature' => $headerSignature]);

        $this->assertFalse($this->signatureResolver->verifySignature($request, $serviceKey));
    }
}
