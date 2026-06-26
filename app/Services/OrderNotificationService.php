<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Mail\OrderStatusMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\WelcomeMail;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    public function welcome(User $user): void
    {
        Mail::to($user->email)->sendNow(new WelcomeMail($user));
    }

    public function orderCreated(Order $order): void
    {
        // Customers are only emailed after payment is confirmed.
    }

    public function paymentReceived(Order $order): void
    {
        $order->loadMissing('items');

        $email = $order->customerEmail();

        if (! $email || $this->isExcludedInbox($email)) {
            return;
        }

        Mail::to($email)->sendNow(new PaymentReceivedMail($order));

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

        if (! $email || $this->isExcludedInbox($email)) {
            return;
        }

        $order->loadMissing('items');

        match ($order->status) {
            OrderStatus::Processing,
            OrderStatus::ReadyForDelivery,
            OrderStatus::Shipped,
            OrderStatus::Delivered => Mail::to($email)->sendNow(new OrderStatusMail($order, $previousStatus)),
            OrderStatus::Cancelled => $this->orderCancelled($order, $previousStatus),
            default => null,
        };
    }

    public function orderCancelledUnpaid(Order $order): void
    {
        // Unpaid cancellations do not trigger customer emails.
    }

    protected function orderCancelled(Order $order, OrderStatus $previousStatus): void
    {
        // Cancelled orders do not trigger customer emails.
    }

    protected function isExcludedInbox(string $email): bool
    {
        $normalized = strtolower(trim($email));

        $excluded = array_filter([
            strtolower(trim((string) config('shop.contact_email'))),
            strtolower(trim((string) config('mail.from.address'))),
        ]);

        if (in_array($normalized, $excluded, true)) {
            return true;
        }

        return User::query()
            ->where('email', $email)
            ->where('role', UserRole::Admin)
            ->exists();
    }
}
