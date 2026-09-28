<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Mail\OrderStatusMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\WelcomeMail;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_does_not_email_the_customer(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => UserRole::Customer,
            'email' => 'customer@example.com',
        ]);

        app(OrderNotificationService::class)->welcome($user);

        Mail::assertNothingOutgoing();
    }

    public function test_status_changes_do_not_email_the_customer(): void
    {
        Mail::fake();

        $order = $this->makeOrder(PaymentStatus::Paid, OrderStatus::Processing);

        app(OrderNotificationService::class)->orderStatusChanged($order, OrderStatus::Paid);

        Mail::assertNothingOutgoing();
        Mail::assertNotOutgoing(OrderStatusMail::class);
    }

    public function test_invoice_email_is_sent_only_after_payment(): void
    {
        Mail::fake();

        $pending = $this->makeOrder(PaymentStatus::Pending, OrderStatus::PendingPayment);
        $paid = $this->makeOrder(PaymentStatus::Paid, OrderStatus::Paid);

        $notifications = app(OrderNotificationService::class);
        $notifications->paymentReceived($pending);
        $notifications->paymentReceived($paid);

        Mail::assertNotOutgoing(WelcomeMail::class);
        Mail::assertOutgoingCount(1);
        Mail::assertSent(PaymentReceivedMail::class, function (PaymentReceivedMail $mail) use ($paid) {
            return $mail->order->is($paid)
                && $mail->hasTo('ada@example.com');
        });
    }

    private function makeOrder(PaymentStatus $paymentStatus, OrderStatus $status): Order
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);

        return Order::query()->create([
            'order_number' => 'SACY-'.fake()->unique()->numerify('####'),
            'user_id' => $user->id,
            'subtotal' => 100,
            'delivery_fee' => 0,
            'tax' => 0,
            'total' => 100,
            'payment_status' => $paymentStatus,
            'status' => $status,
            'billing_full_name' => 'Ada Mensah',
            'billing_phone' => '0240000000',
            'billing_email' => 'ada@example.com',
            'billing_address' => '1 High Street',
            'billing_city' => 'Accra',
            'billing_country' => 'Ghana',
            'paid_at' => $paymentStatus === PaymentStatus::Paid ? now() : null,
        ]);
    }
}
