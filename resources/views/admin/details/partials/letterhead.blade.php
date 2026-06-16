<div class="admin-detail-letterhead mb-6 border-b border-neutral-200 pb-6">
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/brand/logo1.webp') }}" alt="{{ config('shop.store_name') }}" class="h-12 w-auto object-contain">
            <div>
                <p class="text-lg font-semibold uppercase tracking-wide text-brand-black">{{ config('shop.store_name') }}</p>
                <p class="text-sm text-brand-muted">Premium footwear across Ghana</p>
            </div>
        </div>
        <div class="text-right text-sm text-brand-muted">
            <p>{{ config('shop.contact_email') }}</p>
            <p>{{ config('shop.contact_phone') }}</p>
            <p>{{ config('shop.contact_address') }}</p>
            <p>{{ config('shop.contact_website') }}</p>
        </div>
    </div>
</div>
