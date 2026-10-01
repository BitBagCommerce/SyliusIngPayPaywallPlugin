<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace spec\BitBag\SyliusIngPayPlugin\Action;

use ArrayAccess;
use BitBag\SyliusIngPayPlugin\Action\StatusAction;
use BitBag\SyliusIngPayPlugin\Api\IngPayApiInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;

final class StatusActionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(StatusAction::class);
    }

    public function it_should_implement_interface(): void
    {
        $this->shouldImplement(ActionInterface::class);
    }

    public function it_should_return_new_status(
        GetStatusInterface $request,
    ): void {
        $data = ['statusIngPay' => IngPayApiInterface::NEW_STATUS, 'paymentId' => 1];

        $request->getModel()->willReturn(new ArrayCollection($data));

        $request->markNew()->shouldBeCalled();

        $this->execute($request);
    }

    public function it_should_return_pending_status(
        GetStatusInterface $request,
    ): void {
        $data = ['statusIngPay' => IngPayApiInterface::PENDING_STATUS, 'paymentId' => 1];

        $request->getModel()->willReturn(new ArrayCollection($data));
        $request->markPending()->shouldBeCalled();

        $this->execute($request);
    }

    public function it_should_return_cancelled_status(
        GetStatusInterface $request,
    ): void {
        $data = ['statusIngPay' => IngPayApiInterface::CANCELLED_STATUS, 'paymentId' => 1, 'tokenHash' => 'dfgdsgxcvxcerf234'];

        $request->getModel()->willReturn(new ArrayCollection($data));
        $request->markCanceled()->shouldBeCalled();
        $data['tokenHash'] = '';

        $request->setModel(new ArrayCollection($data))->shouldBeCalled();

        $this->execute($request);
    }

    public function it_should_return_rejected_status(
        GetStatusInterface $request,
    ): void {
        $data = ['statusIngPay' => IngPayApiInterface::REJECTED_STATUS, 'paymentId' => 1, 'tokenHash' => 'dfgdsgxcvxcerf234'];
        $request->getModel()->willReturn(new ArrayCollection($data));
        $request->markFailed()->shouldBeCalled();
        $data['tokenHash'] = '';

        $request->setModel(new ArrayCollection($data))->shouldBeCalled();

        $this->execute($request);
    }

    public function it_should_return_settled_status(
        GetStatusInterface $request,
    ): void {
        $data = ['statusIngPay' => IngPayApiInterface::SETTLED_STATUS, 'paymentId' => 1, 'tokenHash' => 'dfgdsgxcvxcerf234'];
        $request->getModel()->willReturn(new ArrayCollection($data));

        $request->markCaptured()->shouldBeCalled();

        $this->execute($request);
    }

    public function it_should_return_unknown_status(
        GetStatusInterface $request,
    ): void {
        $data = ['statusIngPay' => 'test', 'paymentId' => 1, 'tokenHash' => 'dfgdsgxcvxcerf234'];
        $request->getModel()->willReturn(new ArrayCollection($data));

        $request->markUnknown()->shouldBeCalled();

        $this->execute($request);
    }

    function it_throws_exception_if_request_not_supported(
        GetStatusInterface $request,
    ): void {
        $this->shouldThrow(RequestNotSupportedException::class)->during('execute', [$request]);
    }

    function it_returns_true_if_request_is_valid(
        GetStatusInterface $request,
        ArrayAccess $model,
    ): void {
        $request->getModel()->willReturn($model);

        $this->supports($request)->shouldBe(true);
    }

    function it_returns_false_if_request_model_is_empty(
        GetStatusInterface $request,
    ): void {
        $request->getModel()->willReturn(null);

        $this->supports($request)->shouldBe(false);
    }

    function it_returns_false_if_request_class_not_instanceof_GetStatusInterface(
        Request $request,
    ): void {
        $this->supports($request)->shouldBe(false);
    }
}
