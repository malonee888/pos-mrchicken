@extends('layouts.app')

@section('title', 'Pelanggan')
@section('subtitle', 'Kelola data pelanggan untuk transaksi & pengiriman')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <span class="alert-icon">✅</span>
        <div class="alert-body">{{ session('success') }}</div>
    </div>
@endif

<div class="flex-between mb-24">
    <div class="search-bar" style="width:280px;">
        <span>🔍</span>
        <input type="text" id="cari-pelanggan" placeholder="Cari nama / nomor HP..." onkeyup="filterPelanggan()">
    </div>
    <button class="btn btn-primary" onclick="bukaModalTambahPelanggan()">＋ Tambah Pelanggan</button>
</div>

<div class="card mb-0">
    <div class="card-header">
        <div class="card-title">👥 Data Pelanggan</div>
        <div class="card-subtitle">{{ $pelanggan->count() }} pelanggan terdaftar</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>ID</th><th>Nama Pelanggan</th><th>No. HP / WA</th><th>Alamat</th><th>Total Transaksi</th><th>Saldo Hutang</th><th>Status</th><th>Aksi</th>
            </tr></thead>
            <tbody id="tabel-pelanggan">
                @forelse($pelanggan as $item)
                    @php
                        // Total Transaksi & Saldo Hutang dihitung dari tabel lain.
                        // Akan tetap 0 selama tabel transactions/debts belum dibuat (Tahap 10 & 15),
                        // setelah itu otomatis terisi data asli tanpa perlu ubah kode ini.
                        $totalTransaksi = \Illuminate\Support\Facades\Schema::hasTable('transactions')
                            ? \App\Models\Transaction::where('customer_id', $item->id)->sum('total_price')
                            : 0;

                        $saldoHutang = \Illuminate\Support\Facades\Schema::hasTable('debts')
                            ? \App\Models\Debt::where('customer_id', $item->id)->where('status', '!=', 'lunas')->sum('initial_amount')
                              - \App\Models\Debt::where('customer_id', $item->id)->where('status', '!=', 'lunas')->sum('paid_amount')
                            : 0;
                    @endphp
                    <tr class="baris-pelanggan" data-nama="{{ strtolower($item->name) }}" data-hp="{{ $item->phone }}">
                        <td class="font-mono">#PLG-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td class="fw-700">{{ $item->name }}</td>
                        <td>{{ $item->phone ?? '-' }}</td>
                        <td>{{ $item->address ?? '-' }}</td>
                        <td class="font-mono">Rp {{ number_format($totalTransaksi, 0, ',', '.') }}</td>
                        <td class="font-mono fw-700 {{ $saldoHutang > 0 ? 'text-red' : 'text-green' }}">Rp {{ number_format($saldoHutang, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $item->is_active ? 'badge-green' : 'badge-red' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td>
                            <div class="flex-gap">
                                <button class="btn btn-secondary btn-sm btn-icon" onclick="bukaModalEditPelanggan({{ $item->id }})">✏️</button>
                                <button class="btn btn-secondary btn-sm btn-icon" title="Detail pelanggan (segera hadir)">👁</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; color: var(--ink-2); padding: 24px;">
                            Belum ada pelanggan. Klik "＋ Tambah Pelanggan" untuk menambahkan data pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('components.modals.pelanggan')

@endsection

@section('scripts')
<script>
    function bukaModalTambahPelanggan() {
        document.querySelector('#modal-pelanggan .modal-title').textContent = '👤 Tambah Pelanggan Baru';
        document.getElementById('form-pelanggan').action = "{{ route('pelanggan.store') }}";
        document.getElementById('form-pelanggan-method').value = 'POST';
        document.getElementById('form-pelanggan').reset();
        openModal('modal-pelanggan');
    }

    function bukaModalEditPelanggan(id) {
        fetch(`/pelanggan/${id}/edit`)
            .then(res => res.json())
            .then(data => {
                document.querySelector('#modal-pelanggan .modal-title').textContent = '👤 Edit Data Pelanggan';
                document.getElementById('form-pelanggan').action = `/pelanggan/${id}`;
                document.getElementById('form-pelanggan-method').value = 'PUT';
                document.getElementById('input-nama-pelanggan').value = data.name;
                document.getElementById('input-hp-pelanggan').value = data.phone ?? '';
                document.getElementById('input-alamat-pelanggan').value = data.address ?? '';
                document.getElementById('input-jenis-pelanggan').value = data.type;
                openModal('modal-pelanggan');
            });
    }

    // Filter tabel sederhana, murni tampilan (tidak query ulang ke server)
    function filterPelanggan() {
        const kata = document.getElementById('cari-pelanggan').value.toLowerCase();
        document.querySelectorAll('.baris-pelanggan').forEach(baris => {
            const cocok = baris.dataset.nama.includes(kata) || (baris.dataset.hp ?? '').includes(kata);
            baris.style.display = cocok ? '' : 'none';
        });
    }
</script>
@endsection
