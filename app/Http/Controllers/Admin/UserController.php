<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    /** SRS-001 */
    public function index(): View
    {
        $users = User::orderByDesc('created_at')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /** SRS-001: admin menambah akun pengguna baru. */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,user'],
        ]);

        // Password otomatis di-hash oleh cast 'hashed' pada model User.
        User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun pengguna baru berhasil ditambahkan.');
    }

    /** SRS-001: akun yang dihapus otomatis tidak bisa login lagi. */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang login.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun pengguna berhasil dihapus.');
    }
}
