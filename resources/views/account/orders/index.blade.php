@extends('account.layout')

@section('title', 'My Orders - SACYSHOES')
@section('account-heading', 'Track My Orders')
@section('account-subheading', 'Follow payment, processing, shipping, and delivery updates for every order.')

@section('account-content')
    @if ($orders->isEmpty())
        <div class="card p-8 text-center">
            <p class="text-brand-muted">You have not placed any orders yet.</p>
            <a href="{{ route('shop.index') }}" class="btn-primary mt-6 inline-block px-6 py-2.5">
                Browse Shop
            </a>
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="hidden border-b border-neutral-200 bg-brand-light px-6 py-3 text-xs font-medium uppercase tracking-wide text-brand-muted md:grid md:grid-cols-[1.2fr_1fr_1fr_1fr_auto] md:gap-4">
                <span>Order</span>
                <span>Date</span>
                <span>Status</span>
                <span>Total</span>
                <span></span>
            </div>

            <div class="divide-y divide-neutral-100">
                @foreach ($orders as $order)
                    <div class="px-6 py-4 md:grid md:grid-cols-[1.2fr_1fr_1fr_1fr_auto] md:items-center md:gap-4">
                        <div>
                            <p class="font-medium">{{ $order->order_number }}</p>
                            <p class="mt-1 text-sm text-brand-muted md:hidden">
                                {{ $order->created_at->format('M j, Y') }}
                            </p>
                        </div>
                        <p class="hidden text-sm text-brand-muted md:block">
                            {{ $order->created_at->format('M j, Y') }}
                        </p>
                        <p class="mt-2 text-sm md:mt-0">
                            <span class="inline-block border border-neutral-200 px-2 py-1 text-xs font-medium uppercase tracking-wide">
                                {{ $order->status->label() }}
                            </span>
                        </p>
                        <p class="mt-2 font-medium md:mt-0">
                            {{ config('shop.currency_symbol') }} {{ number_format($order->total, 2) }}
                        </p>
                        <div class="mt-3 md:mt-0">
                            <a href="{{ route('account.orders.show', $order) }}" class="btn-outline px-4 py-2 text-sm">
                                Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
