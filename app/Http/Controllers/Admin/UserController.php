<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(): View
    {
        $request = request();
        $usersQuery = $this->usersQuery($request);

        return view('admin.users.index', [
            'users' => (clone $usersQuery)->with('role')
                ->orderBy('name')->paginate(in_array(request()->integer('per_page'), [10, 25, 50], true) ? request()->integer('per_page') : 10)->withQueryString(),
            'roles' => Role::query()->whereIn('name', ['superadmin', 'admin'])->orderBy('id')->get(),
            'userStats' => [
                'total' => (clone $usersQuery)->count(),
                'active' => (clone $usersQuery)->where('status', 'active')->count(),
                'restricted' => (clone $usersQuery)->whereIn('status', ['inactive', 'locked'])->count(),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->usersQuery($request)->with('role')->orderBy('name');

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Nama', 'Email', 'Role', 'Status', 'Login Terakhir', 'Dibuat']);

            $query->chunk(200, function ($users) use ($handle): void {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->name,
                        $user->email,
                        ucfirst($user->role?->name ?? '-'),
                        ucfirst($user->status),
                        $user->last_login_at?->format('Y-m-d H:i:s') ?? '-',
                        $user->created_at?->format('Y-m-d H:i:s') ?? '-',
                    ]);
                }
            });

            fclose($handle);
        }, 'users-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function usersQuery(Request $request): Builder
    {
        return User::query()
            ->when($request->filled('q'), fn (Builder $query): Builder => $query->where(fn (Builder $search): Builder => $search->where('name', 'like', '%'.$request->string('q').'%')->orWhere('email', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('role'), fn (Builder $query): Builder => $query->whereHas('role', fn (Builder $role): Builder => $role->where('name', $request->string('role')->toString())))
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')->toString()));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User,
            'roles' => Role::query()->whereIn('name', ['superadmin', 'admin'])->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role_id' => ['required', Rule::exists('roles', 'id')->whereIn('name', ['superadmin', 'admin'])],
            'password' => [
                'required',
                'confirmed',
                Password::min((int) config('cms.authentication.password_min_length'))->mixedCase()->numbers(),
            ],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $role = Role::query()->findOrFail($validated['role_id']);
        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'role_id' => $role->id,
            'password_hash' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'must_change_password' => $role->name !== 'user',
        ]);
        $activityLogger->log($request->user(), 'create', User::class, $user->id, null, $user->only(['name', 'email', 'role_id', 'status']));

        return redirect()->route('admin.users.index')->with('status', 'Akun berhasil dibuat. Akun CMS wajib mengganti password saat login pertama.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::query()->whereIn('name', ['superadmin', 'admin'])->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,'.$user->id],
            'role_id' => ['required', Rule::exists('roles', 'id')->whereIn('name', ['superadmin', 'admin'])],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $actor = $request->user();
        $newRole = Role::query()->findOrFail($validated['role_id']);

        if ($actor->is($user) && ($validated['status'] !== 'active' || $newRole->name !== 'superadmin')) {
            return back()->withErrors(['role_id' => 'Superadmin tidak dapat menonaktifkan atau menurunkan role akunnya sendiri.'])->withInput();
        }

        if ($user->hasRole('superadmin') && $user->status === 'active' && ($validated['status'] !== 'active' || $newRole->name !== 'superadmin')) {
            $activeSuperadmins = User::query()
                ->whereHas('role', fn (Builder $query): Builder => $query->where('name', 'superadmin'))
                ->where('status', 'active')
                ->count();

            if ($activeSuperadmins <= 1) {
                return back()->withErrors(['role_id' => 'Sistem harus selalu memiliki minimal satu Superadmin aktif.'])->withInput();
            }
        }

        $oldValues = $user->only(['name', 'email', 'role_id', 'status']);
        $user->fill($validated)->save();
        $activityLogger->log($actor, 'update', User::class, $user->id, $oldValues, $user->only(['name', 'email', 'role_id', 'status']));

        return redirect()->route('admin.users.index')->with('status', 'Akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun sendiri.']);
        }

        if ($user->hasRole('superadmin') && $user->status === 'active') {
            $activeSuperadmins = User::query()
                ->whereHas('role', fn (Builder $query): Builder => $query->where('name', 'superadmin'))
                ->where('status', 'active')
                ->count();

            if ($activeSuperadmins <= 1) {
                return back()->withErrors(['user' => 'Sistem harus selalu memiliki minimal satu Superadmin aktif.']);
            }
        }

        $oldValues = $user->only(['name', 'email', 'role_id', 'status']);
        $user->delete();
        $activityLogger->log($request->user(), 'delete', User::class, $user->id, $oldValues);

        return redirect()->route('admin.users.index')->with('status', 'Akun berhasil dihapus.');
    }

    public function resetPassword(Request $request, User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        $temporaryPassword = Str::password(16);
        $user->forceFill([
            'password_hash' => Hash::make($temporaryPassword),
            'must_change_password' => true,
            'failed_login_count' => 0,
            'locked_until' => null,
            'status' => 'active',
        ])->save();
        $activityLogger->log($request->user(), 'update', User::class, $user->id, null, ['password_reset' => true]);

        return redirect()->route('admin.users.index')->with('temporary_password', $temporaryPassword);
    }

    public function unlock(Request $request, User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        $user->forceFill([
            'status' => 'active',
            'failed_login_count' => 0,
            'locked_until' => null,
        ])->save();
        $activityLogger->log($request->user(), 'update', User::class, $user->id, ['status' => 'locked'], ['status' => 'active']);

        return redirect()->route('admin.users.index')->with('status', 'Akun berhasil dibuka.');
    }
}
