<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderCreatedMail;
use App\Mail\OrderStatusMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\WelcomeMail;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\User;
use App\Services\EmailDispatchService;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    public function welcome(User $user): void
    {
        Mail::to($user->email)->queue(new WelcomeMail($user));
    }

    public function orderCreated(Order $order): void
    {
        $email = $order->customerEmail();

        if (! $email) {
            return;
        }

        $order->loadMissing('items');

        Mail::to($email)->queue(new OrderCreatedMail($order));
    }

    public function paymentReceived(Order $order): void
    {
        $order->loadMissing('items');

        $email = $order->customerEmail();

        if (! $email) {
            return;
        }

        Mail::to($email)->queue(new PaymentReceivedMail($order));

        app(EmailDispatchService::class)->log(
            slug: EmailTemplate::SLUG_PAYMENT_RECEIVED,
            recipient: $email,
            orderId: $order->id,
        );
    }

    public function orderStatusChanged(Order $order, OrderStatus $previousStatus): void
    {
        if ($order->status === $previousStatus) {
            return;
        }

        $email = $order->customerEmail();

        if (! $email) {
            return;
        }

        $order->loadMissing('items');

        match ($order->status) {
            OrderStatus::Processing,
            OrderStatus::ReadyForDelivery,
            OrderStatus::Shipped,
            OrderStatus::Delivered => Mail::to($email)->queue(new OrderStatusMail($order, $previousStatus)),
            OrderStatus::Cancelled => $this->orderCancelled($order, $previousStatus),
            default => null,
        };
    }

    public function orderCancelledUnpaid(Order $order): void
    {
        $this->orderCancelled($order, OrderStatus::PendingPayment);
    }

    protected function orderCancelled(Order $order, OrderStatus $previousStatus): void
    {
        if ($previousStatus !== OrderStatus::PendingPayment) {
            return;
        }

        $email = $order->customerEmail();

        if (! $email) {
            return;
        }

        Mail::to($email)->queue(new OrderCancelledMail($order));
    }
}
