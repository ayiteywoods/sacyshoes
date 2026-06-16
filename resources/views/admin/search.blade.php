@extends('layouts.admin')

@section('heading', 'Search')
@section('subheading', $query !== '' ? 'Results for "'.$query.'"' : 'Find products, orders, customers, and categories')

@section('content')
  @php
      $totalResults = $products->count() + $orders->count() + $customers->count() + $categories->count();
  @endphp

  @if ($query === '')
      <div class="card p-8 text-center text-sm text-brand-muted">
          Enter a term in the search bar above to find records across your store.
      </div>
  @elseif ($totalResults === 0)
      <div class="card p-8 text-center">
          <p class="font-medium">No results found for "{{ $query }}"</p>
          <p class="mt-2 text-sm text-brand-muted">Try a product name, order number, customer email, or category.</p>
      </div>
  @else
      <div class="grid gap-6 xl:grid-cols-2">
          @if ($products->isNotEmpty())
              <div class="card overflow-hidden">
                  <div class="border-b border-neutral-200 px-4 py-3 sm:px-6">
                      <h2 class="font-semibold">Products</h2>
                  </div>
                  <ul class="divide-y divide-neutral-100">
                      @foreach ($products as $product)
                          <li>
                              <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center justify-between gap-4 px-4 py-3 text-sm transition hover:bg-brand-light sm:px-6">
                                  <div class="min-w-0">
                                      <p class="truncate font-medium">{{ $product->name }}</p>
                                      <p class="text-brand-muted">{{ $product->sku }} · {{ $product->category->name }}</p>
                                  </div>
                                  <span class="shrink-0 text-brand-red">GHS {{ number_format($product->sellingPrice(), 2) }}</span>
                              </a>
                          </li>
                      @endforeach
                  </ul>
              </div>
          @endif

          @if ($orders->isNotEmpty())
              <div class="card overflow-hidden">
                  <div class="border-b border-neutral-200 px-4 py-3 sm:px-6">
                      <h2 class="font-semibold">Orders</h2>
                  </div>
                  <ul class="divide-y divide-neutral-100">
                      @foreach ($orders as $order)
                          <li>
                              <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between gap-4 px-4 py-3 text-sm transition hover:bg-brand-light sm:px-6">
                                  <div class="min-w-0">
                                      <p class="truncate font-medium">{{ $order->order_number }}</p>
                                      <p class="text-brand-muted">{{ $order->user?->name ?? $order->billing_full_name }}</p>
                                  </div>
                                  <span class="shrink-0">GHS {{ number_format($order->total, 2) }}</span>
                              </a>
                          </li>
                      @endforeach
                  </ul>
              </div>
          @endif

          @if ($customers->isNotEmpty())
              <div class="card overflow-hidden">
                  <div class="border-b border-neutral-200 px-4 py-3 sm:px-6">
                      <h2 class="font-semibold">Customers</h2>
                  </div>
                  <ul class="divide-y divide-neutral-100">
                      @foreach ($customers as $customer)
                          <li class="flex items-center justify-between gap-4 px-4 py-3 text-sm sm:px-6">
                              <div class="min-w-0">
                                  <p class="truncate font-medium">{{ $customer->name }}</p>
                                  <p class="text-brand-muted">{{ $customer->email }}</p>
                              </div>
                              <button
                                  type="button"
                                  class="shrink-0 text-brand-red hover:underline"
                                  @click="openDetail(@js(route('admin.details.customers', $customer)))"
                              >
                                  View
                              </button>
                          </li>
                      @endforeach
                  </ul>
              </div>
          @endif

          @if ($categories->isNotEmpty())
              <div class="card overflow-hidden">
                  <div class="border-b border-neutral-200 px-4 py-3 sm:px-6">
                      <h2 class="font-semibold">Categories</h2>
                  </div>
                  <ul class="divide-y divide-neutral-100">
                      @foreach ($categories as $category)
                          <li>
                              <a href="{{ route('admin.categories.edit', $category) }}" class="flex items-center justify-between gap-4 px-4 py-3 text-sm transition hover:bg-brand-light sm:px-6">
                                  <div class="min-w-0">
                                      <p class="truncate font-medium">{{ $category->name }}</p>
                                      <p class="text-brand-muted">{{ $category->slug }}</p>
                                  </div>
                                  <span class="shrink-0 text-brand-muted">{{ $category->status->label() }}</span>
                              </a>
                          </li>
                      @endforeach
                  </ul>
              </div>
          @endif
      </div>
  @endif
@endsection
