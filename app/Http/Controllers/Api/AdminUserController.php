<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()->with('role')
            ->when($request->filled('q'), fn (Builder $query): Builder => $query->where(fn (Builder $search): Builder => $search->where('name', 'like', '%'.$request->string('q').'%')->orWhere('email', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('role'), fn (Builder $query): Builder => $query->whereHas('role', fn (Builder $role): Builder => $role->where('name', $request->string('role'))))
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')))
            ->orderBy('name')->paginate(25)->withQueryString();

        return response()->json(['success' => true, 'data' => $users, 'message' => 'User berhasil dimuat.']);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $user->load('role'), 'message' => 'User berhasil dimuat.']);
    }

    public function store(Request $request, ActivityLogger $activityLogger): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->whereIn('name', ['superadmin', 'admin'])], 'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'confirmed', Password::min((int) config('cms.authentication.password_min_length'))->mixedCase()->numbers()],
        ]);
        $role = Role::query()->findOrFail($validated['role_id']);
        $user = User::query()->create([
            'name' => $validated['name'], 'email' => strtolower($validated['email']), 'role_id' => $role->id,
            'status' => $validated['status'], 'password_hash' => Hash::make($validated['password']), 'must_change_password' => $role->name !== 'user',
        ]);
        $activityLogger->log($request->user(), 'create', User::class, $user->id, null, $user->only(['name', 'email', 'role_id', 'status']));

        return response()->json(['success' => true, 'data' => $user->load('role'), 'message' => 'User berhasil dibuat dan wajib mengganti password saat login pertama.'], 201);
    }

    public function update(Request $request, User $user, ActivityLogger $activityLogger): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->whereIn('name', ['superadmin', 'admin'])], 'status' => ['required', 'in:active,inactive'],
        ]);
        $actor = $request->user();

        return DB::transaction(function () use ($validated, $actor, $user, $activityLogger): JsonResponse {
            Role::query()->where('name', 'superadmin')->lockForUpdate()->firstOrFail();
            $user->refresh();
            $role = Role::query()->findOrFail($validated['role_id']);

            if ($actor->is($user) && ($validated['status'] !== 'active' || (int) $role->id !== (int) $user->role_id)) {
                return response()->json(['success' => false, 'data' => null, 'message' => 'Anda tidak dapat menonaktifkan atau mengubah role akun sendiri.'], 422);
            }

            if ($user->hasRole('superadmin') && $user->status === 'active' && ($validated['status'] !== 'active' || $role->name !== 'superadmin') && $this->activeSuperadminCount() <= 1) {
                return response()->json(['success' => false, 'data' => null, 'message' => 'Sistem harus selalu memiliki minimal satu Superadmin aktif.'], 422);
            }

            $oldValues = $user->only(['name', 'email', 'role_id', 'status']);
            $user->fill([...$validated, 'email' => strtolower($validated['email'])])->save();
            $activityLogger->log($actor, 'update', User::class, $user->id, $oldValues, $user->only(['name', 'email', 'role_id', 'status']));

            return response()->json(['success' => true, 'data' => $user->load('role'), 'message' => 'User berhasil diperbarui.']);
        });
    }

    public function destroy(Request $request, User $user, ActivityLogger $activityLogger): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Anda tidak dapat menghapus akun sendiri.'], 422);
        }

        return DB::transaction(function () use ($request, $user, $activityLogger): JsonResponse {
            Role::query()->where('name', 'superadmin')->lockForUpdate()->firstOrFail();
            $user->refresh();

            if ($user->hasRole('superadmin') && $user->status === 'active' && $this->activeSuperadminCount() <= 1) {
                return response()->json(['success' => false, 'data' => null, 'message' => 'Sistem harus selalu memiliki minimal satu Superadmin aktif.'], 422);
            }

            $oldValues = $user->only(['name', 'email', 'role_id', 'status']);
            $user->delete();
            $activityLogger->log($request->user(), 'delete', User::class, $user->id, $oldValues);

            return response()->json(['success' => true, 'data' => null, 'message' => 'User berhasil dihapus.']);
        });
    }

    public function resetPassword(Request $request, User $user, ActivityLogger $activityLogger): JsonResponse
    {
        $temporaryPassword = Str::password(18, symbols: false);
        $user->forceFill([
            'password_hash' => Hash::make($temporaryPassword), 'must_change_password' => true,
            'failed_login_count' => 0, 'locked_until' => null, 'status' => 'active',
        ])->save();
        $activityLogger->log($request->user(), 'update', User::class, $user->id, null, ['password_reset' => true]);

        return response()->json(['success' => true, 'data' => ['temporary_password' => $temporaryPassword], 'message' => 'Password sementara dibuat. User wajib menggantinya saat login.']);
    }

    public function unlock(Request $request, User $user, ActivityLogger $activityLogger): JsonResponse
    {
        $user->forceFill(['status' => 'active', 'failed_login_count' => 0, 'locked_until' => null])->save();
        $activityLogger->log($request->user(), 'update', User::class, $user->id, ['status' => 'locked'], ['status' => 'active']);

        return response()->json(['success' => true, 'data' => $user->load('role'), 'message' => 'Akun berhasil dibuka.']);
    }

    private function activeSuperadminCount(): int
    {
        return User::query()->whereHas('role', fn (Builder $query): Builder => $query->where('name', 'superadmin'))->where('status', 'active')->count();
    }
}
