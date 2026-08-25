<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ChangePasswordRequest;
use App\Http\Requests\Settings\UpdateProfileRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function index(): View
    {
        return view('app.settings.account.index', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $oldValues = $user->only(['name', 'email']);

        $user->update($request->validated());

        AuditLogger::log(
            action: 'user.profile_updated',
            entityType: User::class,
            entityId: $user->id,
            oldValues: $oldValues,
            newValues: $request->validated()
        );

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        AuditLogger::log(
            action: 'user.password_changed',
            entityType: User::class,
            entityId: $user->id
        );

        return back()->with('success', 'Password changed successfully.');
    }
}
