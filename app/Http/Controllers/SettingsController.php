<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::instance();
        return view('settings.index', compact('settings'));
    }

    public function updateSystem(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'theme'        => 'nullable|in:default,light,dark',
        ]);

        $settings = SystemSetting::instance();

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $settings->logo_path = $path;
        }

        $settings->company_name        = $request->company_name;
        $settings->company_description = $request->company_description;
        $settings->theme               = $request->theme ?? 'default';
        $settings->save();

        ActivityLog::record('Settings', 'Updated System Info', "Company name updated to {$settings->company_name}.");

        return back()->with('success', 'System information updated.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:4|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        ActivityLog::record('Settings', 'Changed Password', "User {$user->username} changed password.");

        return back()->with('success', 'Password changed successfully.');
    }
    public function backup()
    {
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        $dbHost = env('DB_HOST');

        $filename = 'naturasimis_backup_' . now()->format('Y_m_d_His') . '.sql';
        $path = storage_path('app/' . $filename);

        $command = "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > \"{$path}\"";
        exec($command);

        if (file_exists($path)) {
            return response()->download($path, $filename)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Backup failed. Please try again.');
    }
}   