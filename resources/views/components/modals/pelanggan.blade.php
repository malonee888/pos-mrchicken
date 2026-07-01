<div class="modal-overlay" id="modal-pelanggan">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title">👤 Tambah Pelanggan Baru</div>
        <div class="modal-sub">Data pelanggan untuk pencatatan transaksi & hutang</div>
      </div>
      <button class="btn-close" onclick="closeModal('modal-pelanggan')">✕</button>
    </div>

    <form id="form-pelanggan" method="POST" action="{{ route('pelanggan.store') }}">
        @csrf
        <input type="hidden" name="_method" id="form-pelanggan-method" value="POST">

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

        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Nama Lengkap *</label>
                <input type="text" name="name" id="input-nama-pelanggan" class="finput" placeholder="Nama pelanggan" required>
            </div>
            <div class="fgroup">
                <label class="flabel">No. HP / WhatsApp</label>
                <input type="text" name="phone" id="input-hp-pelanggan" class="finput" placeholder="08xxxxxxxxxx">
            </div>
        </div>
        <div class="fgroup" style="margin-bottom:16px;">
            <label class="flabel">Alamat Pengiriman</label>
            <textarea name="address" id="input-alamat-pelanggan" class="ftextarea" placeholder="Alamat lengkap untuk pengiriman..."></textarea>
        </div>
        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Jenis Pelanggan</label>
                <select name="type" id="input-jenis-pelanggan" class="fselect">
                    <option value="reguler">Pelanggan Reguler</option>
                    <option value="reseller">Reseller</option>
                    <option value="warung">Warung/Usaha</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-pelanggan')">Batal</button>
            <button type="submit" class="btn btn-primary">💾 Simpan Pelanggan</button>
        </div>
    </form>
  </div>
</div>