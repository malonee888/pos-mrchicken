@extends('layouts.app')

@section('title', 'Produk')
@section('subtitle', 'Kelola data produk ayam fillet')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <span class="alert-icon">✅</span>
        <div class="alert-body">{{ session('success') }}</div>
    </div>
@endif

<div class="flex-between mb-24">
    <div></div>
    @if(auth()->user()->role === 'owner')
        <button class="btn btn-primary" onclick="bukaModalTambahProduk()">＋ Tambah Produk</button>
    @endif
</div>

<div class="grid-3" style="margin-bottom:24px;">
    @php
        // Rotasi warna & emoji, supaya tampilan tetap variatif walau jumlah produk dinamis
        $variasiTampilan = [
            ['warna' => 'var(--orange)', 'border' => 'orange', 'emoji' => '🍗'],
            ['warna' => 'var(--amber)', 'border' => 'amber', 'emoji' => '🦵'],
            ['warna' => 'var(--blue)', 'border' => 'blue', 'emoji' => '🦶'],
            ['warna' => 'var(--green)', 'border' => 'green', 'emoji' => '🍖'],
        ];
    @endphp

    @forelse($produk as $index => $item)
        @php
            $variasi = $variasiTampilan[$index % count($variasiTampilan)];

            // Hitung stok = total masuk - total keluar (sesuai rancangan Tahap 2)
            // Akan tetap 0 selama tabel stock_movements belum dibuat (Tahap 12)
            $stokTersedia = \Illuminate\Support\Facades\Schema::hasTable('stock_movements')
                ? \App\Models\StockMovement::where('product_id', $item->id)->where('type', 'masuk')->sum('quantity_kg')
                  - \App\Models\StockMovement::where('product_id', $item->id)->where('type', 'keluar')->sum('quantity_kg')
                : 0;

            $kelasStok = $stokTersedia < 50 ? 'stok-warn' : 'stok-ok';
        @endphp
        <div class="card mb-0" style="border-top:3px solid {{ $variasi['warna'] }};">
            <div style="font-size:2rem;margin-bottom:12px;">{{ $variasi['emoji'] }}</div>
            <div style="font-weight:700;font-size:1rem;">{{ $item->name }}</div>
            <div style="font-family:'JetBrains Mono',monospace;font-size:1.3rem;font-weight:800;color:{{ $variasi['warna'] }};margin:8px 0;">
                Rp {{ number_format($item->price_per_kg, 0, ',', '.') }}<span style="font-size:.7rem;font-weight:400;color:var(--ink-3)">/{{ $item->unit }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.82rem;color:var(--ink-2);">
                <span>Stok: <strong class="{{ $kelasStok }}">{{ number_format($stokTersedia, 0) }} KG</strong></span>
                <span class="badge {{ $item->is_active ? 'badge-green' : 'badge-red' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
            @if(auth()->user()->role === 'owner')
                <div class="flex-gap" style="margin-top:14px;">
                    <button class="btn btn-secondary btn-sm" onclick="bukaModalEditProduk({{ $item->id }})">✏️ Edit Harga</button>
                </div>
            @endif
        </div>
    @empty
        <div class="card mb-0" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
            <p style="color: var(--ink-2);">Belum ada produk. Klik "＋ Tambah Produk" untuk menambahkan produk pertama.</p>
        </div>
    @endforelse
</div>

@include('components.modals.produk')

@endsection

@section('scripts')
<script>
    function bukaModalTambahProduk() {
        document.querySelector('#modal-produk .modal-title').textContent = '🍗 Tambah Produk';
        document.getElementById('form-produk').action = "{{ route('produk.store') }}";
        document.getElementById('form-produk-method').value = 'POST';
        document.getElementById('form-produk').reset();
        openModal('modal-produk');
    }

    function bukaModalEditProduk(id) {
        fetch(`/produk/${id}/edit`)
            .then(res => res.json())
            .then(data => {
                document.querySelector('#modal-produk .modal-title').textContent = '🍗 Edit Produk';
                document.getElementById('form-produk').action = `/produk/${id}`;
                document.getElementById('form-produk-method').value = 'PUT';
                document.getElementById('input-nama-produk').value = data.name;
                document.getElementById('input-harga-produk').value = data.price_per_kg;
                document.getElementById('input-satuan-produk').value = data.unit;
                document.getElementById('input-deskripsi-produk').value = data.description ?? '';
                openModal('modal-produk');
            });
    }
</script>
@endsection