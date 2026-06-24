@foreach ($products as $product)
    @include('storefront.partials.product-card', ['product' => $product])
@endforeach
