<?php

    namespace App\Http\Controllers;

    use App\Models\ActivityLog;
    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;

    class UserController extends Controller
    {
        public function index()
        {
            $users         = User::orderBy('username')->get();
            $archivedUsers = User::onlyTrashed()->orderBy('username')->get();
            return view('users.index', compact('users', 'archivedUsers'));
        }

        public function store(Request $request)
        {
            $request->validate([
        'username' => 'required|string|unique:users,username',
        'role'     => 'required|in:admin,inventory,production,manager',
        'password' => ['required', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*?&]/'],
        'status'   => 'required|in:Active,Inactive',
    ], [
        'password.min'   => 'Password must be at least 8 characters.',
        'password.regex' => 'Password must include uppercase, lowercase, a number, and a special character (@$!%*?&).',
    ]);

            $user = User::create([
                'username' => $request->username,
                'email'    => $request->email,
                'role'     => $request->role,
                'password' => Hash::make($request->password),
                'status'   => $request->status,
            ]);

            ActivityLog::record('Users', 'Added User', "New user {$user->username} ({$user->role}) created.");

            return back()->with('success', "User {$user->username} added successfully.");
        }

        public function update(Request $request, User $user)
    {
        $validator = \Validator::make($request->all(), [
        'username' => 'required|string|unique:users,username,' . $user->id,
        'role'     => 'required|in:admin,inventory,production,manager',
        'status'   => 'required|in:Active,Inactive',
        'password' => ['nullable', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*?&]/'],
    ], [
        'password.min'   => 'Password must be at least 8 characters.',
        'password.regex' => 'Password must include uppercase, lowercase, a number, and a special character (@$!%*?&).',
    ]);

        if ($validator->fails()) {
            return redirect(route('users.index') . '?edit_id=' . $user->id)
                ->withErrors($validator)
                ->withInput();
        }

        $user->username = $request->username;
        $user->email    = $request->email;
        $user->role     = $request->role;
        $user->status   = $request->status;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        ActivityLog::record('Users', 'Updated User', "User {$user->username} updated.");

        return back()->with('success', "User updated successfully.");
    }

        public function destroy(User $user)
        {
            if ($user->id === auth()->id()) {
                return back()->withErrors(['delete' => 'You cannot archive your own account.']);
            }

            if ($user->status === 'Active') {
                return back()->withErrors(['delete' => 'Only inactive users can be archived.']);
            }

            $username = $user->username;
            $user->delete(); // soft delete — sets deleted_at

            ActivityLog::record('Users', 'Archived User', "User {$username} archived.");

            return back()->with('success', "User {$username} archived.");
        }

        public function restore($id)
        {
            $user = User::onlyTrashed()->findOrFail($id);
            $user->restore();

            ActivityLog::record('Users', 'Restored User', "User {$user->username} restored.");

            return back()->with('success', "User {$user->username} restored successfully.");
        }
    }