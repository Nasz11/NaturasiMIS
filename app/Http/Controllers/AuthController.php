<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
                    ->where('status', 'Active')
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['login' => 'Invalid username or password.'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));

        ActivityLog::record('Auth', 'Login', "User {$user->username} logged in.");

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        ActivityLog::record('Auth', 'Logout', "User " . auth()->user()->username . " logged out.");
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function updateAccount(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'username'     => 'required|string|unique:users,username,' . $user->id,
            'email'        => 'nullable|email|unique:users,email,' . $user->id,
            'new_password' => 'nullable|min:4',
            'theme'        => 'nullable|in:default,light,dark',
            'language'     => 'nullable|in:en,tl,es',
        ]);

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = Hash::make($request->new_password);
        }

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $user->profile_picture = $path;
        }

        $user->username               = $request->username;
        $user->email                  = $request->email;
        $user->theme                  = $request->theme ?? 'default';
        $user->language               = $request->language ?? 'en';
        $user->notifications_enabled  = $request->boolean('notifications_enabled');
        $user->two_factor_enabled     = $request->boolean('two_factor_enabled');
        $user->save();

        ActivityLog::record('Auth', 'Updated Account', "User {$user->username} updated account settings.");

        return redirect()->back()->with('success', 'Account settings saved successfully!');
    }
}
