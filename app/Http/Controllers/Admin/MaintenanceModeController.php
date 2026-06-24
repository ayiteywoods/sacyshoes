<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MaintenanceModeRequest;
use App\Models\StoreSetting;
use App\Services\StoreSettingService;
use Illuminate\Http\RedirectResponse;

class MaintenanceModeController extends Controller
{
    public function update(MaintenanceModeRequest $request, StoreSettingService $settingsService): RedirectResponse
    {
        $settings = StoreSetting::current();

        $settings->update($request->validated());

        $settingsService->applyToConfig();

        $status = $settings->maintenance_mode
            ? 'Maintenance mode enabled. The storefront is now hidden from customers.'
            : 'Maintenance mode disabled. The storefront is live again.';

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $status);
    }
}
