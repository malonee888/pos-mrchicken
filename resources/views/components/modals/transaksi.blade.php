<div class="modal-overlay" id="modal-transaksi">
  <div class="modal modal-lg">
    <div class="modal-header">
      <div>
        <div class="modal-title">🛒 Catat Transaksi Penjualan Baru</div>
        <div class="modal-sub">Isi detail pesanan pelanggan dari WhatsApp/telepon</div>
      </div>
      <button class="btn-close" onclick="closeModal('modal-transaksi')">✕</button>
    </div>

    <form id="form-transaksi" method="POST" action="{{ route('transaksi.store') }}">
        @csrf

        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Pelanggan *</label>
                <select name="customer_id" class="fselect" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    @foreach($pelanggan as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fgroup">
                <label class="flabel">Slot Pengiriman</label>
                <select name="delivery_slot_id" class="fselect">
                    <option value="">-- Tanpa Slot --</option>
                    @foreach($slot as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="divider">
        <div style="font-size:.85rem;font-weight:700;color:var(--ink-2);margin-bottom:12px;">Detail Produk</div>

        <div id="product-lines">
            {{-- Baris produk diisi otomatis lewat JavaScript (tambahBarisProduk) saat modal dibuka --}}
        </div>
        <button type="button" class="btn btn-secondary btn-sm" onclick="tambahBarisProduk()" style="margin-bottom:16px;">＋ Tambah Produk</button>

        <hr class="divider">
        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Metode Pembayaran</label>
                <select name="payment_method" class="fselect" id="metode-bayar" onchange="toggleHutang()">
                    <option value="lunas">✅ Lunas (Tunai/Transfer)</option>
                    <option value="hutang">📒 Hutang</option>
                    <option value="dp">💳 DP (Uang Muka)</option>
                </select>
            </div>
            <div class="fgroup" id="field-dp" style="display:none;">
                <label class="flabel">Jumlah DP / Uang Muka</label>
                <div class="finput-prefix">
                    <span class="prefix">Rp</span>
                    <input type="number" name="down_payment" class="finput" placeholder="0" min="0">
                </div>
            </div>
        </div>
        <div class="fgroup" style="margin-bottom:16px;">
            <label class="flabel">Catatan Pesanan</label>
            <textarea name="notes" class="ftextarea" placeholder="Misal: minta dipotong kecil, kirim sebelum jam 10..."></textarea>
        </div>

        <div style="background:var(--orange-bg);border:1px solid var(--orange);border-radius:var(--radius-sm);padding:16px;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-size:.8rem;color:var(--ink-2);font-weight:600;">TOTAL PEMBAYARAN</div>
                <div id="total-display" style="font-family:'JetBrains Mono',monospace;font-size:1.4rem;font-weight:800;color:var(--orange);">Rp 0</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:.8rem;color:var(--ink-2);font-weight:600;">TOTAL KG</div>
                <div id="total-kg" style="font-size:1.2rem;font-weight:700;color:var(--ink);">0 KG</div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-transaksi')">Batal</button>
            <button type="submit" class="btn btn-primary">💾 Simpan Transaksi</button>
        </div>
    </form>
  </div>
</div>
