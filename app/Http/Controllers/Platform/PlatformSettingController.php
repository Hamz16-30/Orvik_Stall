<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlatformSettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'default_trial_days' => PlatformSetting::get('default_trial_days', 14),
            'default_currency' => PlatformSetting::get('default_currency', 'PKR'),
            'default_plan' => PlatformSetting::get('default_plan', 'starter'),
            'maintenance_mode' => (bool) PlatformSetting::get('maintenance_mode', false),
            'registration_enabled' => (bool) PlatformSetting::get('registration_enabled', true),
            'ads_enabled' => (bool) PlatformSetting::get('ads_enabled', false),
            'adsense_publisher_id' => PlatformSetting::get('adsense_publisher_id', ''),
        ];

        return view('platform.settings.index', [
            'settings' => $settings,
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'default_trial_days' => 'required|integer|min:1|max:365',
            'default_currency' => 'required|string|max:10',
            'default_plan' => 'required|string|exists:plans,slug',
            'adsense_publisher_id' => 'nullable|string|max:100',
        ]);

        PlatformSetting::set('default_trial_days', (int) $request->default_trial_days, 'integer', 'Standard trial duration');
        PlatformSetting::set('default_currency', $request->default_currency, 'string', 'Platform base currency');
        PlatformSetting::set('default_plan', $request->default_plan, 'string', 'Default trial plan');
        PlatformSetting::set('maintenance_mode', $request->has('maintenance_mode'), 'boolean', 'Maintenance mode status');
        PlatformSetting::set('registration_enabled', $request->has('registration_enabled'), 'boolean', 'New registrations status');
        PlatformSetting::set('ads_enabled', $request->has('ads_enabled'), 'boolean', 'AdSense monetizations');
        PlatformSetting::set('adsense_publisher_id', $request->adsense_publisher_id ?? '', 'string', 'AdSense publisher ID');

        AuditLogger::log('platform.settings_updated', 'PlatformSetting', null, $request->except('_token'), null, Auth::id());

        return back()->with('success', 'Platform settings saved successfully.');
    }
}
