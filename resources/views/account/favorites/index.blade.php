@extends('account.layout')

@section('title', 'Favourites - SACYSHOES')
@section('account-heading', 'Favourites')
@section('account-subheading', 'Products you have saved for later.')

@section('account-content')
    @if ($products->isEmpty())
        <div class="card p-8 text-center">
            <p class="text-brand-muted">You have not saved any products yet.</p>
            <a href="{{ route('shop.index') }}" class="btn-primary mt-6 inline-block px-6 py-2.5">
                Browse Shop
            </a>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                @include('storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    @endif
@endsection
