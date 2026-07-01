<div class="modal-overlay" id="modal-stok">
  <div class="modal">
    <div class="modal-header">
      <div><div class="modal-title">📦 Tambah Stok Masuk</div><div class="modal-sub">Stok berkurang otomatis saat pengiriman selesai</div></div>
      <button class="btn-close" onclick="closeModal('modal-stok')">✕</button>
    </div>

    <div class="alert alert-info"><span class="alert-icon">ℹ️</span><div class="alert-body">Pengurangan stok hanya terjadi saat status pengiriman diubah ke <strong>Selesai</strong>, bukan saat order masuk.</div></div>

    <form id="form-stok" method="POST" action="{{ route('stok.store') }}">
        @csrf

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:16px;">
                <span class="alert-icon">⚠️</span>
                <div class="alert-body">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="fgroup" style="margin-bottom:16px;">
            <label class="flabel">Produk *</label>
            <select name="product_id" id="select-produk-stok" class="fselect" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($produk as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Jumlah Stok Masuk (KG) *</label>
                <input type="number" name="quantity_kg" class="finput" placeholder="0.0" step="0.1" min="0.1" required>
            </div>
            <div class="fgroup">
                <label class="flabel">Tanggal Masuk</label>
                <input type="date" class="finput" value="{{ today()->format('Y-m-d') }}" disabled>
                <small style="color:var(--ink-3);">Otomatis tanggal hari ini</small>
            </div>
        </div>
        <div class="fgroup" style="margin-bottom:16px;">
            <label class="flabel">Keterangan</label>
            <input type="text" name="note" class="finput" placeholder="Misal: Stok pagi dari supplier">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-stok')">Batal</button>
            <button type="submit" class="btn btn-primary">💾 Simpan Stok</button>
        </div>
    </form>
  </div>
</div>