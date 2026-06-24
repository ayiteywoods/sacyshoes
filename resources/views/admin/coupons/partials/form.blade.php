@php
    use App\Enums\CouponType;

    $coupon = $coupon ?? null;
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-form-label :required="true">Code</x-form-label>
        <input type="text" name="code" value="{{ old('code', $coupon?->code) }}" required class="input-field uppercase" autocomplete="off">
        @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <x-form-label :required="true">Type</x-form-label>
        <select name="type" required class="input-field">
            @foreach (CouponType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('type', $coupon?->type?->value) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <x-form-label :required="true">Value</x-form-label>
        <input type="number" step="0.01" name="value" value="{{ old('value', $coupon?->value) }}" required class="input-field">
        <p class="mt-1 text-xs text-brand-muted">Percentage or fixed amount in GHS.</p>
        @error('value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Minimum order amount (GHS)</label>
        <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', $coupon?->min_order_amount) }}" class="input-field">
        @error('min_order_amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Maximum discount (GHS)</label>
        <input type="number" step="0.01" name="max_discount" value="{{ old('max_discount', $coupon?->max_discount) }}" class="input-field">
        @error('max_discount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Usage limit</label>
        <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon?->usage_limit) }}" class="input-field">
        @error('usage_limit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Starts at</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $coupon?->starts_at?->format('Y-m-d\TH:i')) }}" class="input-field">
        @error('starts_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Expires at</label>
        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $coupon?->expires_at?->format('Y-m-d\TH:i')) }}" class="input-field">
        @error('expires_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" class="rounded-none border-neutral-300 text-brand-red focus:ring-brand-red" @checked(old('is_active', $coupon?->is_active ?? true))>
            Active
        </label>
    </div>
</div>
