<div class="modal-overlay" id="modal-pengguna">
  <div class="modal">
    <div class="modal-header">
      <div><div class="modal-title">⚙️ Tambah Akun Pengguna</div></div>
      <button class="btn-close" onclick="closeModal('modal-pengguna')">✕</button>
    </div>

    <form id="form-pengguna" method="POST" action="{{ route('pengguna.store') }}">
        @csrf
        <input type="hidden" name="_method" id="form-pengguna-method" value="POST">

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

        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Nama Lengkap *</label>
                <input type="text" name="name" class="finput" placeholder="Nama pengguna" required>
            </div>
            <div class="fgroup">
                <label class="flabel">Username *</label>
                <input type="text" name="username" class="finput" placeholder="username (tanpa spasi)" required>
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Password *</label>
                <input type="password" name="password" id="input-password-pengguna" class="finput" placeholder="Minimal 8 karakter" required>
            </div>
            <div class="fgroup">
                <label class="flabel">Peran / Role *</label>
                <select name="role" class="fselect">
                    <option value="karyawan">👤 Karyawan</option>
                    <option value="owner">👑 Owner</option>
                </select>
            </div>
        </div>
        <div class="alert alert-warning" style="margin-top:12px;">
            <span class="alert-icon">🔒</span>
            <div class="alert-body">Password disimpan dalam format terenkripsi (bcrypt). Karyawan tidak bisa mengakses Laporan & Manajemen Pengguna.</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-pengguna')">Batal</button>
            <button type="submit" class="btn btn-primary">💾 Simpan Pengguna</button>
        </div>
    </form>
  </div>
</div>