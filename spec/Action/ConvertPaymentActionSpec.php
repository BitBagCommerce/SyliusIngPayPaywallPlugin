<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace spec\BitBag\SyliusIngPayPlugin\Action;

use BitBag\SyliusIngPayPlugin\Action\ConvertPaymentAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;

final class ConvertPaymentActionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConvertPaymentAction::class);
    }

    function it_implements_ing_pay_gateway_factory_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    public function it_sets_result_from_payment_details_with_non_empty_details(
        Convert $request,
        PaymentInterface $payment,
    ): void {
        $request->getSource()->willReturn($payment);
        $payment->getDetails()->willReturn(['field' => '123']);
        $request->setResult(['field' => '123'])->shouldBeCalled();

        $this->execute($request);
    }

    public function it_sets_empty_result_when_payment_details_are_empty(
        Convert $request,
        PaymentInterface $payment,
    ): void {
        $payment->getDetails()->willReturn([]);
        $request->getSource()->willReturn($payment);
        $request->setResult([])->shouldBeCalled();

        $this->execute($request);
    }

    public function it_sets_result_when_payment_details_contain_null(
        Convert $request,
        PaymentInterface $payment,
    ): void {
        $payment->getDetails()->willReturn(['key' => null]);
        $request->getSource()->willReturn($payment);
        $request->setResult(['key' => null])->shouldBeCalled();

        $this->execute($request);
    }

    public function it_should_return_true_when_getTo_and_source_is_valid(
        Convert $request,
        PaymentInterface $payment,
    ): void {
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $this->supports($request)->shouldReturn(true);
    }

    public function it_should_return_false_when_source_is_invalid(
        Convert $request,
    ): void {
        $request->getSource()->willReturn(null);
        $request->getTo()->willReturn('array');

        $this->supports($request)->shouldReturn(false);
    }

    public function it_should_return_false_when_getTo_is_invalid(
        Convert $request,
        PaymentInterface $payment,
    ): void {
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('object');

        $this->supports($request)->shouldReturn(false);
    }

    public function it_should_return_false_when_getTo_and_source_is_invalid(
        Convert $request,
    ): void {
        $request->getSource()->willReturn(null);
        $request->getTo()->willReturn('object');

        $this->supports($request)->shouldReturn(false);
    }

    public function it_should_return_false_when_request_invalid(
        Request $request,
    ): void {
        $this->supports($request)->shouldReturn(false);
    }
}
