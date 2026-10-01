<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace spec\BitBag\SyliusIngPayPlugin;

use BitBag\SyliusIngPayPlugin\IngPayGatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use PhpSpec\ObjectBehavior;

final class IngPayGatewayFactorySpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(IngPayGatewayFactory::class);
    }

    function it_implements_ing_pay_gateway_factory_interface(): void
    {
        $this->shouldHaveType(GatewayFactoryInterface::class);
    }
}
