<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()->latest()->paginate(15)->withQueryString();
        $data = ['users' => $users];

        if (! $request->ajax()) {
            $data['roleCounts'] = [
                'admin' => User::where('role', 'admin')->count(),
                'kasir' => User::where('role', 'kasir')->count(),
                'pelanggan' => User::where('role', 'pelanggan')->count(),
            ];
        }

        return $this->viewOrFragment($request, 'admin.role-user', 'admin.role-user-results', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'kasir', 'pelanggan'])],
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => now(),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Membuat user {$user->name} ({$user->email}) dengan role {$user->role}",
            'old_values' => null,
            'new_values' => ['name' => $user->name, 'email' => $user->email, 'role' => $user->role],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => 'Data berhasil ditambahkan'])
            : redirect()->route('admin.user-role.index')->with('swal_success', 'Data berhasil ditambahkan');
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', Rule::in(['admin', 'kasir', 'pelanggan'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $oldValues = ['name' => $user->name, 'email' => $user->email, 'role' => $user->role];

        $user->fill([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $oldValues['password'] = '*****';
        }

        $user->save();

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Memperbarui user {$user->name}",
            'old_values' => $oldValues,
            'new_values' => ['name' => $user->name, 'email' => $user->email, 'role' => $user->role],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => 'Data berhasil diperbarui'])
            : redirect()->route('admin.user-role.index')->with('swal_success', 'Data berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('swal_error', 'Tidak bisa menghapus akun sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Menghapus user {$userName}",
            'old_values' => ['name' => $userName],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.user-role.index')->with('swal_success', 'Data berhasil dihapus');
    }

    /**
     * Buat tautan reset password untuk satu user dan kembalikan tautannya,
     * supaya admin bisa mengirimkannya manual (mis. lewat WhatsApp).
     *
     * Email reset tidak pernah dikirim otomatis: alur /forgot-password sudah
     * dihapus, jadi token baru membatalkan token lama milik user yang sama.
     */
    public function resetLink(Request $request, User $user)
    {
        $token = Password::createToken($user);
        $url = route('password.reset', ['token' => $token, 'email' => $user->email]);
        $expiresInMinutes = (int) config('auth.passwords.users.expire', 60);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_password_reset_link',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Membuat tautan reset password untuk {$user->name} ({$user->email})",
            'old_values' => null,
            'new_values' => ['email' => $user->email, 'expires_in_minutes' => $expiresInMinutes],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $request->expectsJson()
            ? response()->json([
                'message' => 'Tautan reset password berhasil dibuat.',
                'url' => $url,
                'email' => $user->email,
                'expires_in_minutes' => $expiresInMinutes,
            ])
            : redirect()->route('admin.user-role.index')
                ->with('swal_success', 'Tautan reset password untuk '.$user->email.' berhasil dibuat.');
    }
}
