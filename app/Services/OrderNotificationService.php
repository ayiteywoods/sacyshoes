<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\OrderCreatedMail;
use App\Mail\OrderStatusMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\WelcomeMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    public function welcome(User $user): void
    {
        Mail::to($user->email)->send(new WelcomeMail($user));
    }

    public function orderCreated(Order $order): void
    {
        Mail::to($order->billing_email)->send(new OrderCreatedMail($order));
    }

    public function paymentReceived(Order $order): void
    {
        Mail::to($order->billing_email)->send(new PaymentReceivedMail($order));
    }

    public function orderStatusUpdated(Order $order, OrderStatus $previousStatus): void
    {
        if ($order->status === $previousStatus) {
            return;
        }

        if (! in_array($order->status, [OrderStatus::Shipped, OrderStatus::Delivered], true)) {
            return;
        }

        Mail::to($order->billing_email)->send(new OrderStatusMail($order, $previousStatus));
    }
}
