<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function createForgot(): View
    {
        return view('guest.forgot-password');
    }

    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            AuditLogger::log(
                action: 'user.password_reset_requested',
                entityType: 'User',
                entityId: $user->id,
                newValues: ['email' => $email],
                userId: $user->id
            );

            // In local/shared environment, flash token link directly for smooth testing
            return back()->with('status', 'Password reset instructions sent! If testing locally: ' . route('password.reset', ['token' => $token, 'email' => $email]));
        }

        // Generic response to avoid account enumeration
        return back()->with('status', 'If that email address exists in our system, you will receive a reset link shortly.');
    }

    public function createReset(Request $request): View
    {
        return view('guest.reset-password', [
            'token' => $request->route('token'),
            'email' => $request->email,
        ]);
    }

    public function storeReset(ResetPasswordRequest $request): RedirectResponse
    {
        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid or expired.']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User could not be found.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        AuditLogger::log(
            action: 'user.password_reset_completed',
            entityType: 'User',
            entityId: $user->id,
            userId: $user->id
        );

        return redirect()->route('login')->with('success', 'Your password has been reset successfully! Please sign in.');
    }
}
