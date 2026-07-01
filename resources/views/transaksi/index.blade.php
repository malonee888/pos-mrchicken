@extends('layouts.app')

@section('title', 'Pesan Ayam')
@section('subtitle', 'Catat transaksi penjualan dari pelanggan')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <span class="alert-icon">✅</span>
        <div class="alert-body">{{ session('success') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <span class="alert-icon">⚠️</span>
        <div class="alert-body">
            <div class="alert-title">Transaksi gagal disimpan</div>
            <ul style="margin:6px 0 0 16px; padding:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            @if($errors->has('stok'))
                <a href="{{ route('preorder.index') }}" class="btn btn-primary btn-sm" style="margin-top:10px;">📋 Buka Pre-Order</a>
            @endif
        </div>
    </div>
@endif

<div class="flex-between mb-24">
    <div></div>
    <button class="btn btn-primary" onclick="bukaModalTambahTransaksi()">＋ Catat Transaksi Baru</button>
</div>

<div class="card mb-0">
    <div class="card-header">
        <div class="card-title">🛒 Daftar Transaksi</div>
        <div class="card-subtitle">{{ $transaksi->count() }} transaksi tercatat</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>ID</th><th>Pelanggan</th><th>Produk</th><th>Total KG</th><th>Total Harga</th><th>Pembayaran</th><th>Status</th><th>Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($transaksi as $trx)
                    @php
                        $kelasBayar = match($trx->payment_method) {
                            'lunas' => 'badge-green',
                            'hutang' => 'badge-red',
                            default => 'badge-amber',
                        };
                        $labelBayar = match($trx->payment_method) {
                            'lunas' => '✓ Lunas',
                            'hutang' => 'Hutang',
                            default => 'DP',
                        };
                        $kelasStatus = match($trx->delivery_status) {
                            'selesai' => 'badge-green',
                            'terkirim', 'dalam_perjalanan' => 'badge-blue',
                            'batal' => 'badge-red',
                            default => 'badge-amber',
                        };

                        // Gabungkan nama produk jadi 1 teks ringkas, sesuai catatan dari Tahap 7
                        $namaProduk = $trx->items->count() > 1
                            ? $trx->items->first()->product->name . ' +' . ($trx->items->count() - 1) . ' lainnya'
                            : ($trx->items->first()->product->name ?? '-');
                    @endphp
                    <tr>
                        <td class="font-mono">{{ $trx->transaction_code }}</td>
                        <td>{{ $trx->customer->name ?? '-' }}</td>
                        <td>{{ $namaProduk }}</td>
                        <td>{{ number_format($trx->total_kg, 1) }} KG</td>
                        <td class="font-mono fw-700">Rp {{ number_format($trx->total_price, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $kelasBayar }}">{{ $labelBayar }}</span></td>
                        <td><span class="badge {{ $kelasStatus }}">{{ ucfirst(str_replace('_', ' ', $trx->delivery_status)) }}</span></td>
                        <td>
                            <div class="flex-gap">
                                <button class="btn btn-secondary btn-sm btn-icon" onclick="lihatStruk({{ $trx->id }})" title="Lihat Struk">🧾</button>

                                @if($trx->delivery_status === 'proses')
                                    <button class="btn btn-primary btn-sm" onclick="ubahStatus({{ $trx->id }}, 'dalam_perjalanan')">🚚 Kirim</button>
                                    @if(auth()->user()->role === 'owner')
                                        <button class="btn btn-danger btn-sm btn-icon" onclick="batalkanTransaksi({{ $trx->id }})" title="Batalkan">✕</button>
                                    @endif
                                @elseif($trx->delivery_status === 'dalam_perjalanan')
                                    <button class="btn btn-primary btn-sm" onclick="ubahStatus({{ $trx->id }}, 'terkirim')">✅ Tandai Terkirim</button>
                                    @if(auth()->user()->role === 'owner')
                                        <button class="btn btn-danger btn-sm btn-icon" onclick="batalkanTransaksi({{ $trx->id }})" title="Batalkan">✕</button>
                                    @endif
                                @elseif($trx->delivery_status === 'terkirim')
                                    <button class="btn btn-primary btn-sm" onclick="ubahStatus({{ $trx->id }}, 'selesai')">🏁 Selesaikan</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; color: var(--ink-2); padding: 24px;">
                            Belum ada transaksi. Klik "＋ Catat Transaksi Baru" untuk mencatat pesanan pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('components.modals.transaksi')
@include('components.modals.struk')

@endsection

@section('scripts')
<script>
    let barIdx = 0;

    // Data produk dikirim dari server, dipakai untuk mengisi <option> setiap baris baru
    const daftarProduk = @json($produkUntukJs);

    function bukaModalTambahTransaksi() {
        document.getElementById('form-transaksi').reset();
        document.getElementById('product-lines').innerHTML = '';
        barIdx = 0;
        tambahBarisProduk();
        updateTotal();
        openModal('modal-transaksi');
    }

    function buatOptionProduk() {
        let html = '<option value="">-- Pilih Produk --</option>';
        daftarProduk.forEach(p => {
            html += `<option value="${p.id}" data-harga="${p.harga}">${p.name} (Rp ${Number(p.harga).toLocaleString('id-ID')}/${p.unit})</option>`;
        });
        return html;
    }

    function tambahBarisProduk() {
        barIdx++;
        const div = document.createElement('div');
        div.className = 'product-line';
        div.innerHTML = `
            <div class="fn"><select name="product_id[]" class="fselect" onchange="hitungSubtotal(this)" required>${buatOptionProduk()}</select></div>
            <div class="fkg"><input type="number" name="qty_kg[]" class="finput" placeholder="KG" min="0.1" step="0.1" onchange="hitungSubtotal(this)" required></div>
            <div class="fprice"><input type="text" class="finput font-mono" placeholder="Harga/kg" readonly></div>
            <div class="fsub" style="font-family:'JetBrains Mono',monospace;font-size:.82rem;font-weight:700;color:var(--orange);">Rp 0</div>
            <div class="fdel"><button type="button" class="btn btn-danger btn-sm btn-icon" onclick="hapusBarisProduk(this)">✕</button></div>
        `;
        document.getElementById('product-lines').appendChild(div);
    }

    function hapusBarisProduk(btn) {
        const lines = document.getElementById('product-lines').children;
        if (lines.length <= 1) { showToast('Minimal 1 produk!', 'error'); return; }
        btn.closest('.product-line').remove();
        updateTotal();
    }

    function hitungSubtotal(el) {
        const line = el.closest('.product-line');
        const sel = line.querySelector('select');
        const kg = parseFloat(line.querySelector('input[type=number]')?.value) || 0;
        const harga = parseFloat(sel.selectedOptions[0]?.dataset.harga) || 0;
        line.querySelector('.finput.font-mono').value = harga ? 'Rp ' + harga.toLocaleString('id-ID') : '';
        const sub = harga * kg;
        line.querySelector('.fsub').textContent = 'Rp ' + sub.toLocaleString('id-ID');
        updateTotal();
    }

    function updateTotal() {
        let total = 0, kg = 0;
        document.querySelectorAll('#product-lines .product-line').forEach(line => {
            const sel = line.querySelector('select');
            const kgVal = parseFloat(line.querySelector('input[type=number]')?.value) || 0;
            const harga = parseFloat(sel.selectedOptions[0]?.dataset.harga) || 0;
            total += harga * kgVal;
            kg += kgVal;
        });
        document.getElementById('total-display').textContent = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('total-kg').textContent = kg.toFixed(1) + ' KG';
    }

    function toggleHutang() {
        const v = document.getElementById('metode-bayar').value;
        document.getElementById('field-dp').style.display = v === 'dp' ? 'block' : 'none';
    }

    function lihatStruk(id) {
        fetch(`/transaksi/${id}`)
            .then(res => res.json())
            .then(data => {
                const tanggal = new Date(data.created_at).toLocaleString('id-ID', {
                    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                let baris = '';
                data.items.forEach(item => {
                    baris += `<div style="display:flex;justify-content:space-between;">
                        <span>${item.product.name} × ${item.qty_kg}kg</span>
                        <span>Rp ${Number(item.subtotal).toLocaleString('id-ID')}</span>
                    </div>`;
                });

                const labelBayar = data.payment_method === 'lunas' ? 'LUNAS' : (data.payment_method === 'dp' ? 'DP' : 'HUTANG');

                document.getElementById('isi-struk').innerHTML = `
                    <div style="text-align:center;font-weight:700;font-size:1rem;margin-bottom:8px;">🐔 MR. CHICKEN</div>
                    <div style="text-align:center;color:var(--ink-3);margin-bottom:12px;">POS & Distribution System</div>
                    <div style="border-top:1px dashed var(--border);padding-top:10px;">
                        <div>ID: ${data.transaction_code}</div>
                        <div>Tgl: ${tanggal}</div>
                        <div>Pelanggan: ${data.customer.name}</div>
                    </div>
                    <div style="border-top:1px dashed var(--border);padding-top:10px;margin-top:10px;">${baris}</div>
                    <div style="border-top:1px dashed var(--border);padding-top:10px;margin-top:10px;">
                        <div style="display:flex;justify-content:space-between;font-weight:700;"><span>TOTAL</span><span>Rp ${Number(data.total_price).toLocaleString('id-ID')}</span></div>
                        <div style="display:flex;justify-content:space-between;"><span>Pembayaran</span><span>${labelBayar}</span></div>
                    </div>
                    <div style="border-top:1px dashed var(--border);margin-top:10px;padding-top:10px;text-align:center;color:var(--ink-3);">Terima kasih atas kepercayaan Anda!</div>
                `;
                openModal('modal-struk');
            });
    }

    function ubahStatus(id, statusBaru) {
        const labelKonfirmasi = {
            'dalam_perjalanan': 'Kirim pesanan ini sekarang?',
            'terkirim': 'Tandai pesanan ini sebagai sudah diterima pelanggan?',
            'selesai': 'Selesaikan transaksi ini sebagai sudah tuntas?',
        };

        if (!confirm(labelKonfirmasi[statusBaru] ?? 'Ubah status pesanan ini?')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/transaksi/${id}/status`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" name="delivery_status" value="${statusBaru}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function batalkanTransaksi(id) {
        if (!confirm('Yakin ingin membatalkan transaksi ini?')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/transaksi/${id}/status`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" name="delivery_status" value="batal">
        `;
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection
