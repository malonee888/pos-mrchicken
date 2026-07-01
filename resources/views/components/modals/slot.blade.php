<div class="modal-overlay" id="modal-slot">
  <div class="modal">
    <div class="modal-header">
      <div><div class="modal-title">⚙️ Atur Slot Pengiriman</div></div>
      <button class="btn-close" onclick="closeModal('modal-slot')">✕</button>
    </div>

    <form id="form-slot" method="POST" action="{{ route('pengiriman.store') }}">
        @csrf
        <input type="hidden" name="_method" id="form-slot-method" value="POST">

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
            <label class="flabel">Nama Slot *</label>
            <input type="text" name="name" class="finput" placeholder="Misal: Pagi, Sore, Malam" required>
        </div>
        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Jam Mulai *</label>
                <input type="time" name="start_time" class="finput" required>
            </div>
            <div class="fgroup">
                <label class="flabel">Jam Selesai *</label>
                <input type="time" name="end_time" class="finput" required>
            </div>
        </div>
        <div class="fgroup" style="margin-bottom:16px;">
            <label class="flabel">Kapasitas Maksimal (KG) *</label>
            <input type="number" name="max_capacity_kg" class="finput" placeholder="60" min="1" step="0.1" required>
        </div>
        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Batas Normal (KG) *</label>
                <input type="number" name="normal_threshold_kg" class="finput" placeholder="30" min="0" step="0.1" required>
                <small style="color:var(--ink-3);">Di bawah angka ini = status Normal</small>
            </div>
            <div class="fgroup">
                <label class="flabel">Batas Hampir Penuh (KG) *</label>
                <input type="number" name="almost_full_threshold_kg" class="finput" placeholder="45" min="0" step="0.1" required>
                <small style="color:var(--ink-3);">Di atasnya = status Overload</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-slot')">Batal</button>
            <button type="submit" class="btn btn-primary">💾 Simpan Slot</button>
        </div>
    </form>
  </div>
</div>