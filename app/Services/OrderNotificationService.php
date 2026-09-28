<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Mail\PaymentReceivedMail;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    public function welcome(User $user): void
    {
        // Customers only receive the invoice email after payment.
    }

    public function orderCreated(Order $order): void
    {
        // Customers only receive the invoice email after payment.
    }

    public function paymentReceived(Order $order): void
    {
        $order->loadMissing('items');

        if ($order->payment_status !== PaymentStatus::Paid) {
            return;
        }

        $email = $order->customerEmail();

        if (! $email || $this->isExcludedInbox($email)) {
            return;
        }

        // Queue if possible; never block payment confirmation on SMTP.
        Mail::to($email)->send(new PaymentReceivedMail($order));

        app(EmailDispatchService::class)->log(
            slug: EmailTemplate::SLUG_PAYMENT_RECEIVED,
            recipient: $email,
            orderId: $order->id,
        );
    }

    public function orderStatusChanged(Order $order, OrderStatus $previousStatus): void
    {
        // Customers only receive the invoice email after payment.
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
