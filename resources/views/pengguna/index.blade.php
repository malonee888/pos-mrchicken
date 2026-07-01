@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('subtitle', 'Kelola akun yang dapat mengakses sistem')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <span class="alert-icon">✅</span>
        <div class="alert-body">{{ session('success') }}</div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <span class="alert-icon">⚠️</span>
        <div class="alert-body">{{ session('error') }}</div>
    </div>
@endif

<div class="flex-between mb-24">
    <div></div>
    <button class="btn btn-primary" onclick="bukaModalTambahPengguna()">＋ Tambah Pengguna</button>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">⚙️ Manajemen Akun Pengguna</div>
        <div class="card-subtitle">Hanya Owner yang dapat mengelola akun</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Nama</th><th>Username</th><th>Peran</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($pengguna as $u)
                    <tr>
                        <td class="font-mono">#USR-{{ str_pad($u->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td class="fw-700">{{ $u->name }}</td>
                        <td class="font-mono">{{ $u->username }}</td>
                        <td>
                            @if($u->role === 'owner')
                                <span class="badge badge-orange">👑 Owner</span>
                            @else
                                <span class="badge badge-blue">👤 Karyawan</span>
                            @endif
                        </td>
                        <td>
                            @if($u->is_active)
                                <span class="badge badge-green">Aktif</span>
                            @else
                                <span class="badge badge-gray">Nonaktif</span>
                            @endif
                        </td>
                        <td>{{ $u->last_login_at ? $u->last_login_at->translatedFormat('d M Y, H:i') : 'Belum pernah login' }}</td>
                        <td>
                            <div class="flex-gap">
                                <button class="btn btn-secondary btn-sm btn-icon" onclick="bukaModalEditPengguna({{ $u->id }})" title="Edit">✏️</button>
                                @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('pengguna.toggleActive', $u->id) }}" onsubmit="return confirm('{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $u->name }}?')">
                                        @csrf
                                        <input type="hidden" name="_method" value="PATCH">
                                        @if($u->is_active)
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Nonaktifkan">🔒</button>
                                        @else
                                            <button type="submit" class="btn btn-success btn-sm btn-icon" title="Aktifkan">🔓</button>
                                        @endif
                                    </form>
                                @else
                                    <span class="text-muted" style="font-size:.75rem;" title="Tidak bisa menonaktifkan akun sendiri">(Anda)</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color: var(--ink-2); padding: 24px;">
                            Belum ada pengguna terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('components.modals.pengguna')

@endsection

@section('scripts')
<script>
    function bukaModalTambahPengguna() {
        document.querySelector('#modal-pengguna .modal-title').textContent = '⚙️ Tambah Akun Pengguna';
        document.getElementById('form-pengguna').action = "{{ route('pengguna.store') }}";
        document.getElementById('form-pengguna-method').value = 'POST';
        document.getElementById('form-pengguna').reset();
        document.getElementById('input-password-pengguna').placeholder = 'Minimal 8 karakter';
        document.getElementById('input-password-pengguna').required = true;
        openModal('modal-pengguna');
    }

    function bukaModalEditPengguna(id) {
        fetch(`/pengguna/${id}/edit`)
            .then(res => res.json())
            .then(data => {
                document.querySelector('#modal-pengguna .modal-title').textContent = '⚙️ Edit Akun Pengguna';
                document.getElementById('form-pengguna').action = `/pengguna/${id}`;
                document.getElementById('form-pengguna-method').value = 'PUT';
                document.querySelector('#form-pengguna input[name=name]').value = data.name;
                document.querySelector('#form-pengguna input[name=username]').value = data.username;
                document.querySelector('#form-pengguna select[name=role]').value = data.role;
                document.getElementById('input-password-pengguna').value = '';
                document.getElementById('input-password-pengguna').placeholder = 'Kosongkan jika tidak ingin mengubah password';
                document.getElementById('input-password-pengguna').required = false;
                openModal('modal-pengguna');
            });
    }
</script>
@endsection