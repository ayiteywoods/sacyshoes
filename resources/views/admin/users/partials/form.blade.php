@php
    use App\Enums\AdminPermission;

    $selectedPermissions = old('admin_permissions', $user?->admin_permissions ?? []);
    $isSuperAdmin = old('is_super_admin', $user ? $user->admin_permissions === null : true);
@endphp

<div>
    <x-form-label :required="true">Full name</x-form-label>
    <input type="text" name="name" value="{{ old('name', $user?->name) }}" required class="input-field">
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <x-form-label :required="true">Email</x-form-label>
    <input type="email" name="email" value="{{ old('email', $user?->email) }}" required class="input-field">
    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium">Phone</label>
    <input type="text" name="phone" value="{{ old('phone', $user?->phone) }}" class="input-field">
    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <x-form-label :required="!$user">Password {{ $user ? '(leave blank to keep current)' : '' }}</x-form-label>
    <input type="password" name="password" class="input-field" {{ $user ? '' : 'required' }}>
    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <x-form-label :required="!$user">Confirm password</x-form-label>
    <input type="password" name="password_confirmation" class="input-field" {{ $user ? '' : 'required' }}>
</div>

<div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" id="is_active" class="h-4 w-4 rounded border-neutral-300 text-brand-red" @checked(old('is_active', $user?->is_active ?? true))>
    <label for="is_active" class="text-sm">Active</label>
</div>
@error('is_active')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

<div class="rounded-lg border border-neutral-200 p-4" x-data="{ fullAccess: @json((bool) $isSuperAdmin) }">
    <div class="flex items-center gap-2">
        <input type="hidden" name="is_super_admin" value="0">
        <input
            type="checkbox"
            name="is_super_admin"
            value="1"
            id="is_super_admin"
            class="h-4 w-4 rounded border-neutral-300 text-brand-red"
            x-model="fullAccess"
            @checked($isSuperAdmin)
        >
        <label for="is_super_admin" class="text-sm font-medium">Full access (super admin)</label>
    </div>
    <p class="mt-1 text-xs text-brand-muted">Super admins can access every area of the dashboard.</p>

    <div class="mt-4 space-y-3" x-show="!fullAccess" x-cloak>
        <p class="text-sm font-medium">Permissions</p>
        @foreach ($permissions as $permission)
            <label class="flex items-start gap-3 rounded-lg border border-neutral-100 p-3">
                <input
                    type="checkbox"
                    name="admin_permissions[]"
                    value="{{ $permission->value }}"
                    class="mt-0.5 h-4 w-4 rounded border-neutral-300 text-brand-red"
                    :disabled="fullAccess"
                    @checked(in_array($permission->value, $selectedPermissions ?? [], true))
                >
                <span>
                    <span class="block text-sm font-medium">{{ $permission->label() }}</span>
                    <span class="mt-0.5 block text-xs text-brand-muted">{{ $permission->description() }}</span>
                </span>
            </label>
        @endforeach
        @error('admin_permissions')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
