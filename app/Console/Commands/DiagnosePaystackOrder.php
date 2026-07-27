<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaystackPaymentReconciliationService;
use App\Services\PaystackService;
use App\Support\OrderLookup;
use Illuminate\Console\Command;

class DiagnosePaystackOrder extends Command
{
    protected $signature = 'orders:diagnose-paystack {order : Order number or ID}';

    protected $description = 'Show why a Paystack order may still be pending';

    public function handle(
        PaystackService $paystack,
        PaystackPaymentReconciliationService $reconciliation,
    ): int {
        $input = (string) $this->argument('order');

        $order = OrderLookup::findByNumberOrId($input);

        if (! $order) {
            $this->error("Order not found for \"{$input}\".");
            $this->line('Tip: use the order number shown in admin (e.g. 1534), or id:123 for database ID.');
            $this->newLine();
            $this->line('Recent unpaid orders:');

            Order::query()
                ->where('payment_status', '!=', PaymentStatus::Paid)
                ->latest('id')
                ->limit(10)
                ->get(['id', 'order_number', 'payment_status', 'status', 'created_at'])
                ->each(function (Order $candidate) {
                    $this->line("  #{$candidate->order_number} (id {$candidate->id}) — {$candidate->payment_status->label()} — {$candidate->created_at->format('M j, Y g:i A')}");
                });

            return self::FAILURE;
        }

        $this->info("Order {$order->order_number} (ID {$order->id})");
        $this->line("Status: {$order->status->label()}");
        $this->line("Payment status: {$order->payment_status->label()}");
        $this->line('Total: '.config('shop.currency_symbol').number_format((float) $order->total, 2));
        $this->line('Customer email: '.($order->customerEmail() ?? $order->billing_email));
        $this->line('Paystack mode: '.$paystack->mode());
        $this->line('Callback URL: '.$paystack->callbackUrl());
        $this->newLine();

        $payments = Payment::query()
            ->where('order_id', $order->id)
            ->orderByDesc('id')
            ->get();

        if ($payments->isEmpty()) {
            $this->warn('No payment records found for this order.');
        }

        $hasSuccessfulPaystackPayment = false;

        foreach ($payments as $payment) {
            $this->line("Payment #{$payment->id}: {$payment->reference} ({$payment->status->value})");

            try {
                $data = $paystack->verify($payment->reference);
                $paystackStatus = (string) ($data['status'] ?? 'unknown');
                $this->line("  Paystack status: {$paystackStatus}");

                if ($paystackStatus === 'success') {
                    $hasSuccessfulPaystackPayment = true;
                } elseif ($paystackStatus === 'abandoned') {
                    $this->warn('  Customer opened Paystack but did not complete this payment attempt.');
                } elseif ($paystackStatus === 'failed') {
                    $this->warn('  This payment attempt failed on Paystack.');
                }
            } catch (\Throwable $exception) {
                $this->error('  Verify failed: '.$exception->getMessage());
            }
        }

        $this->newLine();
        $this->info('Searching Paystack for successful transactions linked to this order...');

        $matches = $reconciliation->findSuccessfulPaystackTransactionsForOrder($order);

        if ($matches === []) {
            $this->warn('No successful Paystack transactions found for this order in the last 30 days.');
        } else {
            foreach ($matches as $match) {
                $this->line("  Reference: {$match['reference']} — {$match['status']} — GHS {$match['amount']}");
                $hasSuccessfulPaystackPayment = $hasSuccessfulPaystackPayment || $match['status'] === 'success';
            }
        }

        $this->newLine();

        if ($order->payment_status !== PaymentStatus::Paid) {
            if (! $hasSuccessfulPaystackPayment) {
                $this->error('Conclusion: Paystack has no successful payment for this order.');
                $this->line('The customer likely did not finish paying, or paid under a different order.');
                $this->line('Ask them to pay again from the order page, or send the Paystack receipt reference if they insist they paid.');

                return self::FAILURE;
            }

            $this->info('Attempting reconciliation...');

            if ($reconciliation->reconcileOrder($order)) {
                $order->refresh();
                $this->info('Reconciliation succeeded. Payment status is now: '.$order->payment_status->label());
            } else {
                $this->error('A successful Paystack payment exists but could not be applied. Try:');
                $this->line('  php artisan orders:reconcile-paystack --reference=PAYSTACK_REFERENCE');
            }
        }

        return self::SUCCESS;
    }
}
