<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Role;
use App\Models\User;
use App\Models\PlatformSetting;
use App\Services\AuditLogger;
use App\Services\SaaS\SubscriptionService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        $registrationEnabled = (bool) PlatformSetting::get('registration_enabled', true);

        return view('guest.register', [
            'registrationEnabled' => $registrationEnabled,
        ]);
    }

    public function store(RegisterRequest $request, TenantContext $tenantContext, SubscriptionService $subscriptionService): RedirectResponse
    {
        if (!PlatformSetting::get('registration_enabled', true)) {
            return back()->withErrors([
                'email' => 'New business registrations are temporarily unavailable.',
            ]);
        }

        $user = DB::transaction(function () use ($request, $tenantContext, $subscriptionService) {
            // 1. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            // 2. Create Business
            $business = Business::create([
                'name' => $request->business_name,
                'owner_id' => $user->id,
                'email' => $user->email,
                'currency' => 'PKR',
                'timezone' => 'Asia/Karachi',
                'status' => 'active',
            ]);

            // 3. Assign Owner Role
            $ownerRole = Role::where('name', Role::OWNER)->firstOrFail();

            BusinessUser::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'role_id' => $ownerRole->id,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // 4. Provision Trial Subscription
            $subscriptionService->startTrial($business);

            // Set Context
            $tenantContext->setCurrentBusiness($business);

            // 5. Audit Log
            AuditLogger::log(
                action: 'user.registered',
                entityType: Business::class,
                entityId: $business->id,
                newValues: ['user_name' => $user->name, 'business_name' => $business->name],
                businessId: $business->id,
                userId: $user->id
            );

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('app.dashboard')->with('success', 'Stall account created successfully!');
    }
}
