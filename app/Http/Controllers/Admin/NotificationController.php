<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function show(AdminNotification $notification, AdminNotificationService $notifications): RedirectResponse
    {
        $notifications->markAsRead($notification);

        return redirect()->to($notification->url);
    }

    public function destroy(AdminNotification $notification, AdminNotificationService $notifications): RedirectResponse
    {
        $notifications->dismiss($notification);

        return back()->with('success', 'Notification dismissed.');
    }

    public function destroyAll(AdminNotificationService $notifications): RedirectResponse
    {
        $notifications->dismissAll();

        return back()->with('success', 'All notifications dismissed.');
    }
}
