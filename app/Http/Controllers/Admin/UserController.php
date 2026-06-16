<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPermission;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use App\Support\AdminTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = AdminTable::paginate(
            User::query()->where('role', UserRole::Admin),
            $request,
            [
                'name' => 'name',
                'email' => 'email',
                'phone' => 'phone',
                'is_active' => 'is_active',
                'created_at' => 'created_at',
            ],
        );

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'permissions' => AdminPermission::all(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString() ?: null,
            'password' => $request->string('password')->toString(),
            'role' => UserRole::Admin,
            'is_active' => $request->boolean('is_active', true),
            'admin_permissions' => $this->resolvedPermissions($request),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Admin user created successfully.');
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === UserRole::Admin, 404);

        return view('admin.users.edit', [
            'user' => $user,
            'permissions' => AdminPermission::all(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Admin, 404);

        $data = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString() ?: null,
            'is_active' => $request->boolean('is_active'),
            'admin_permissions' => $this->resolvedPermissions($request),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->string('password')->toString();
        }

        if ($user->id === auth()->id() && ! $request->boolean('is_active')) {
            return back()
                ->withInput()
                ->with('error', 'You cannot deactivate your own account.');
        }

        if ($this->wouldRemoveLastUserManager($user, $data)) {
            return back()
                ->withInput()
                ->with('error', 'At least one active admin with user-management access is required.');
        }

        if ($user->id === auth()->id() && ! $this->userCanManageUsers($data['admin_permissions'])) {
            return back()
                ->withInput()
                ->with('error', 'You cannot remove your own user-management permission.');
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Admin user updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Admin, 404);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if (User::query()->where('role', UserRole::Admin)->where('is_active', true)->count() <= 1 && $user->is_active) {
            return back()->with('error', 'At least one active admin user is required.');
        }

        if ($user->is_active && $this->isLastUserManager($user)) {
            return back()->with('error', 'At least one active admin with user-management access is required.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Admin user removed successfully.');
    }

    /**
     * @return list<string>|null
     */
    private function resolvedPermissions(UserRequest $request): ?array
    {
        if ($request->boolean('is_super_admin')) {
            return null;
        }

        return collect($request->input('admin_permissions', []))
            ->unique()
            ->values()
            ->all();
    }

    private function userCanManageUsers(?array $permissions): bool
    {
        if ($permissions === null) {
            return true;
        }

        return in_array(AdminPermission::Users->value, $permissions, true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function wouldRemoveLastUserManager(User $user, array $data): bool
    {
        if (! $data['is_active']) {
            return $this->isLastUserManager($user);
        }

        if ($this->userCanManageUsers($data['admin_permissions'])) {
            return false;
        }

        return $this->isLastUserManager($user);
    }

    private function isLastUserManager(User $user): bool
    {
        $managers = User::query()
            ->where('role', UserRole::Admin)
            ->where('is_active', true)
            ->get()
            ->filter(fn (User $admin) => $admin->hasAdminPermission(AdminPermission::Users));

        return $managers->count() === 1 && $managers->first()->is($user);
    }
}
