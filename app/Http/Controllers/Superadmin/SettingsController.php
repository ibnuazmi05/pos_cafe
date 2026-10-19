<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        Gate::authorize('manage-settings');

        return view('superadmin.settings', [
            'settings' => [
                'cafe_name' => Setting::get('cafe_name', ''),
                'cafe_address' => Setting::get('cafe_address', ''),
                'tax_enabled' => Setting::get('tax_enabled', '0') === '1',
                'tax_rate' => (float) Setting::get('tax_rate', 0),
                'discount' => (float) Setting::get('discount', 0),
            ],
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $new = [
            'cafe_name' => $data['cafe_name'],
            'cafe_address' => $data['cafe_address'] ?? '',
            'tax_enabled' => $request->boolean('tax_enabled') ? '1' : '0',
            'tax_rate' => (string) (float) $data['tax_rate'],
            'discount' => (string) (float) $data['discount'],
        ];

        $changes = [];
        foreach ($new as $key => $value) {
            $old = (string) Setting::get($key, '');
            if ($old !== $value) {
                $changes[$key] = ['from' => $old, 'to' => $value];
                Setting::set($key, $value);
            }
        }

        if ($changes) {
            ActivityLogger::log('settings.updated', null, $changes);
        }

        return back()->with('success', $changes
            ? 'Pengaturan disimpan. Berlaku untuk transaksi berikutnya.'
            : 'Tidak ada perubahan.');
    }
}
