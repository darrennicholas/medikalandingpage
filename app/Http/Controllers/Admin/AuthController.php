<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse|Response
    {
        if ($request->user()?->status === 'active' && $request->user()->canAccessCms()) {
            return redirect()->to(route('admin.dashboard', absolute: false));
        }

        return response()
            ->view('admin.auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache');
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $email = strtolower($credentials['email']);
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $activityLogger->log(null, 'login_failed', User::class, null, null, ['email' => $email]);

            return $this->loginFailure($request, 'Email atau password salah.');
        }

        if ($user->status === 'locked' && $user->locked_until?->isFuture()) {
            $activityLogger->log($user, 'login_failed', User::class, $user->id, null, ['reason' => 'locked']);

            return $this->loginFailure($request, 'Email atau password salah.');
        }

        if ($user->status === 'locked' && ($user->locked_until === null || $user->locked_until->isPast())) {
            $user->forceFill(['status' => 'active', 'locked_until' => null, 'failed_login_count' => 0])->save();
        }

        if ($user->status === 'inactive') {
            $activityLogger->log($user, 'login_failed', User::class, $user->id, null, ['reason' => 'inactive']);

            return $this->loginFailure($request, 'Email atau password salah.');
        }

        if (! Hash::check($credentials['password'], $user->password_hash)) {
            $failedLoginCount = $user->failed_login_count + 1;
            $shouldLock = $failedLoginCount >= (int) config('cms.authentication.max_failed_attempts');
            $lockedUntil = $shouldLock ? now()->addMinutes((int) config('cms.authentication.lockout_minutes')) : null;

            $user->forceFill([
                'failed_login_count' => $shouldLock ? 0 : $failedLoginCount,
                'status' => $shouldLock ? 'locked' : 'active',
                'locked_until' => $lockedUntil,
            ])->save();
            $activityLogger->log($user, 'login_failed', User::class, $user->id, null, [
                'failed_login_count' => $failedLoginCount,
                'locked_until' => $lockedUntil?->toIso8601String(),
            ]);

            return $this->loginFailure($request, 'Email atau password salah.');
        }

        if (! $user->canAccessCms()) {
            $activityLogger->log($user, 'login_failed', User::class, $user->id);

            return $this->loginFailure($request, 'Email atau password salah.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill([
            'failed_login_count' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ])->save();
        $activityLogger->log($user, 'login', User::class, $user->id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => ['redirect_url' => route('admin.dashboard', absolute: false)],
                'message' => 'Login berhasil.',
            ]);
        }

        return redirect()->to(route('admin.dashboard', absolute: false))->with('status', 'Login berhasil.');
    }

    public function apiLogin(Request $request, ActivityLogger $activityLogger): JsonResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $email = strtolower($credentials['email']);
        $user = User::query()->where('email', $email)->first();

        if ($user !== null && ($user->status === 'inactive' || ($user->status === 'locked' && $user->locked_until?->isFuture()))) {
            $activityLogger->log($user, 'login_failed', User::class, $user->id, null, ['reason' => $user->status]);

            return $this->apiLoginFailure();
        }

        if ($user?->status === 'locked' && ($user->locked_until === null || $user->locked_until->isPast())) {
            $user->forceFill(['status' => 'active', 'locked_until' => null, 'failed_login_count' => 0])->save();
        }

        if ($user === null || ! Hash::check($credentials['password'], $user->password_hash)) {
            if ($user !== null) {
                $failedLoginCount = $user->failed_login_count + 1;
                $shouldLock = $failedLoginCount >= (int) config('cms.authentication.max_failed_attempts');
                $lockedUntil = $shouldLock ? now()->addMinutes((int) config('cms.authentication.lockout_minutes')) : null;
                $user->forceFill([
                    'failed_login_count' => $shouldLock ? 0 : $failedLoginCount,
                    'status' => $shouldLock ? 'locked' : $user->status,
                    'locked_until' => $lockedUntil,
                ])->save();
                $activityLogger->log($user, $shouldLock ? 'auth.locked' : 'login_failed', User::class, $user->id);
            } else {
                $activityLogger->log(null, 'login_failed', User::class, null, null, ['email' => $email]);
            }

            return $this->apiLoginFailure();
        }

        if ($user->status !== 'active') {
            $activityLogger->log($user, 'login_failed', User::class, $user->id, null, ['reason' => $user->status]);

            return $this->apiLoginFailure();
        }

        if (! $user->canAccessCms()) {
            $activityLogger->log($user, 'login_failed', User::class, $user->id);

            return $this->apiLoginFailure();
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['failed_login_count' => 0, 'locked_until' => null, 'last_login_at' => now()])->save();
        $activityLogger->log($user, 'login', User::class, $user->id);

        return response()->json(['success' => true, 'data' => ['user' => $user->load('role'), 'must_change_password' => $user->must_change_password], 'message' => 'Login berhasil.']);
    }

    public function apiLogout(Request $request, ActivityLogger $activityLogger): JsonResponse
    {
        $user = $request->user();
        $activityLogger->log($user, 'logout', User::class, $user?->id);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'data' => null, 'message' => 'Logout berhasil.']);
    }

    public function apiUpdatePassword(Request $request, ActivityLogger $activityLogger): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min((int) config('cms.authentication.password_min_length'))->mixedCase()->numbers()],
        ]);
        $user = $request->user();
        $user->forceFill(['password_hash' => Hash::make($validated['password']), 'must_change_password' => false])->save();
        $activityLogger->log($user, 'update', User::class, $user->id, null, ['password_changed' => true]);

        return response()->json(['success' => true, 'data' => null, 'message' => 'Password berhasil diubah.']);
    }

    public function destroy(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $user = $request->user();
        $activityLogger->log($user, 'logout', User::class, $user?->id);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function loginFailure(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $message,
                'errors' => ['email' => [$message]],
            ], 422);
        }

        return back()->withErrors(['email' => $message])->onlyInput('email');
    }

    private function apiLoginFailure(): JsonResponse
    {
        $message = 'Email atau password salah.';

        return response()->json(['success' => false, 'data' => null, 'message' => $message, 'errors' => ['email' => [$message]]], 422);
    }

    public function editPassword(Request $request): View
    {
        $user = $request->user()->loadMissing('role');

        return view('admin.profile.password', [
            'user' => $user,
        ]);
    }

    public function updatePassword(Request $request, ActivityLogger $activityLogger): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min((int) config('cms.authentication.password_min_length'))->mixedCase()->numbers(),
            ],
        ]);
        $user = $request->user();
        $user->forceFill([
            'password_hash' => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();
        $activityLogger->log($user, 'update', User::class, $user->id, null, ['password_changed' => true]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => ['redirect_url' => route('admin.dashboard', absolute: false)],
                'message' => 'Password berhasil diubah.',
            ]);
        }

        return redirect()->to(route('admin.dashboard', absolute: false))->with('status', 'Password berhasil diubah.');
    }
}
