<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $pengguna = User::orderBy('role')->orderBy('name')->get();

        return view('pengguna.index', compact('pengguna'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
            'role' => 'required|in:owner,karyawan',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return response()->json($user->only(['id', 'name', 'username', 'role']));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:owner,karyawan',
        ]);

        // Cegah owner mengubah rolenya sendiri jadi karyawan (akan terkunci dari fitur owner)
        if ($user->id === auth()->id() && $validated['role'] !== 'owner') {
            return redirect()->route('pengguna.index')->with('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');
        }

        // Password OPSIONAL saat edit - hanya di-update kalau diisi
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function toggleActive(User $user)
    {
        // Cegah owner menonaktifkan akunnya sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('pengguna.index')->with('error', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $pesan = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('pengguna.index')->with('success', "Pengguna berhasil {$pesan}.");
    }
}