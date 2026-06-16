@extends('account.layout')

@section('title', 'Dashboard - SACYSHOES')
@section('account-heading', 'Dashboard')
@section('account-subheading', 'Welcome back, ' . auth()->user()->first_name . '.')

@section('account-content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4">
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-brand-muted">Total Orders</p>
            <p class="mt-2 text-3xl font-semibold text-brand-black">{{ $stats['total'] }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-brand-muted">In Progress</p>
            <p class="mt-2 text-3xl font-semibold text-brand-black">{{ $stats['pending'] }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-brand-muted">Completed</p>
            <p class="mt-2 text-3xl font-semibold text-brand-black">{{ $stats['completed'] }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-brand-muted">Awaiting Payment</p>
            <p class="mt-2 text-3xl font-semibold text-brand-red">{{ $stats['awaiting_payment'] }}</p>
        </div>
    </div>

    <div class="card mt-8 p-6">
        <div class="flex items-center justify-between gap-4">
            <h2 class="page-heading">Recent Orders</h2>
            <a href="{{ route('account.orders.index') }}" class="text-sm font-medium text-brand-red hover:underline">
                View all
            </a>
        </div>

        @if ($recentOrders->isEmpty())
            <p class="mt-6 text-sm text-brand-muted">You have not placed any orders yet.</p>
            <a href="{{ route('shop.index') }}" class="btn-primary mt-4 inline-block px-6 py-2.5">
                Start Shopping
            </a>
        @else
            <div class="mt-6 divide-y divide-neutral-100">
                @foreach ($recentOrders as $order)
                    <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-medium">{{ $order->order_number }}</p>
                            <p class="mt-1 text-sm text-brand-muted">
                                {{ $order->created_at->format('M j, Y') }} · {{ $order->status->label() }}
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <p class="font-medium">
                                {{ config('shop.currency_symbol') }} {{ number_format($order->total, 2) }}
                            </p>
                            <a href="{{ route('account.orders.show', $order) }}" class="btn-outline px-4 py-2 text-sm">
                                View
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
