@extends('layouts.app')

@section('title', 'Stok Produk')
@section('subtitle', 'Pantau dan kelola stok produk ayam fillet')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <span class="alert-icon">✅</span>
        <div class="alert-body">{{ session('success') }}</div>
    </div>
@endif

@if($produkStokRendah->count() > 0)
    <div class="alert alert-warning" style="margin-bottom: 20px;">
        <span class="alert-icon">⚠️</span>
        <div class="alert-body">
            <div class="alert-title">Peringatan Stok Rendah</div>
            @foreach($produkStokRendah as $p)
                {{ $p->name }} hanya tersisa <strong>{{ number_format($p->stok_saat_ini, 0) }} KG</strong>.
            @endforeach
            Pertimbangkan penambahan stok segera.
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div class="card-title">📦 Manajemen Stok Produk</div>
        @if(auth()->user()->role === 'owner')
            <button class="btn btn-primary btn-sm" onclick="bukaModalStok()">＋ Tambah Stok Masuk</button>
        @endif
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Produk</th><th>Stok Saat Ini</th><th>Harga/KG</th><th>Estimasi Nilai</th><th>Status Stok</th><th>Update Terakhir</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($produk as $p)
                    @php
                        $statusRendah = $p->stok_saat_ini < 50;
                        $nilaiEstimasi = $p->stok_saat_ini * $p->price_per_kg;
                    @endphp
                    <tr>
                        <td><div class="fw-700">🍗 {{ $p->name }}</div></td>
                        <td class="font-mono fw-700 {{ $statusRendah ? 'text-red' : 'text-green' }}">{{ number_format($p->stok_saat_ini, 0) }} KG</td>
                        <td class="font-mono">Rp {{ number_format($p->price_per_kg, 0, ',', '.') }}</td>
                        <td class="font-mono">Rp {{ number_format($nilaiEstimasi, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $statusRendah ? 'badge-red' : 'badge-green' }}">{{ $statusRendah ? '⚠ Rendah' : '✓ Aman' }}</span></td>
                        <td>{{ $p->update_terakhir ? $p->update_terakhir->diffForHumans() : '-' }}</td>
                        <td>
                            @if(auth()->user()->role === 'owner')
                                <button class="btn {{ $statusRendah ? 'btn-primary' : 'btn-secondary' }} btn-sm" onclick="bukaModalStok({{ $p->id }})">+ Tambah</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color: var(--ink-2); padding: 24px;">
                            Belum ada produk aktif. Tambahkan produk dulu di menu Produk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><div class="card-title">📜 Riwayat Perubahan Stok</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Waktu</th><th>Produk</th><th>Jenis</th><th>Jumlah</th><th>Keterangan</th><th>Petugas</th></tr></thead>
            <tbody>
                @forelse($riwayat as $r)
                    <tr>
                        <td>{{ $r->created_at->diffForHumans() }}</td>
                        <td>{{ $r->product->name ?? '-' }}</td>
                        <td>
                            @if($r->type === 'masuk')
                                <span class="badge badge-green">+ Masuk</span>
                            @else
                                <span class="badge badge-red">- Keluar</span>
                            @endif
                        </td>
                        <td class="font-mono">{{ $r->type === 'masuk' ? '+' : '-' }} {{ number_format($r->quantity_kg, 0) }} KG</td>
                        <td>{{ $r->note }}</td>
                        <td>{{ $r->user->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; color: var(--ink-2); padding: 24px;">
                            Belum ada riwayat perubahan stok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('components.modals.stok')

@endsection

@section('scripts')
<script>
    function bukaModalStok(productId = null) {
        document.getElementById('form-stok').reset();
        if (productId) {
            document.getElementById('select-produk-stok').value = productId;
        }
        openModal('modal-stok');
    }
</script>
@endsection