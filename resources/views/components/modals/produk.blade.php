<div class="modal-overlay" id="modal-produk">
  <div class="modal">
    <div class="modal-header">
      <div><div class="modal-title">🍗 Tambah / Edit Produk</div></div>
      <button class="btn-close" onclick="closeModal('modal-produk')">✕</button>
    </div>

    <form id="form-produk" method="POST" action="{{ route('produk.store') }}">
        @csrf
        <input type="hidden" name="_method" id="form-produk-method" value="POST">

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:16px;">
                <span class="alert-icon">⚠️</span>
                <div class="alert-body">
                    <div class="alert-title">Periksa kembali isian Anda</div>
                    <ul style="margin:6px 0 0 16px; padding:0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="fgroup" style="margin-bottom:16px;">
            <label class="flabel">Nama Produk *</label>
            <input type="text" name="name" id="input-nama-produk" class="finput" placeholder="Nama produk ayam" required>
        </div>
        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Harga per KG *</label>
                <div class="finput-prefix">
                    <span class="prefix">Rp</span>
                    <input type="number" name="price_per_kg" id="input-harga-produk" class="finput" placeholder="0" min="0" step="0.01" required>
                </div>
            </div>
            <div class="fgroup">
                <label class="flabel">Satuan</label>
                <select name="unit" id="input-satuan-produk" class="fselect">
                    <option value="kg">KG</option>
                    <option value="gram">Gram</option>
                    <option value="ekor">Ekor</option>
                </select>
            </div>
        </div>
        <div class="fgroup" style="margin-bottom:16px;">
            <label class="flabel">Deskripsi</label>
            <textarea name="description" id="input-deskripsi-produk" class="ftextarea" placeholder="Deskripsi produk..."></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-produk')">Batal</button>
            <button type="submit" class="btn btn-primary">💾 Simpan Produk</button>
        </div>
    </form>
  </div>
</div>