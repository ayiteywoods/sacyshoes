<?php

namespace App\View\Components;

use App\Services\AdminNotificationService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class AdminNotifications extends Component
{
    public Collection $adminNotifications;

    public int $adminNotificationCount;

    public function __construct(AdminNotificationService $notifications)
    {
        $this->adminNotifications = $notifications->get();
        $this->adminNotificationCount = $this->adminNotifications->count();
    }

    public function render(): View|Closure|string
    {
        return view('components.admin-notifications');
    }
}
