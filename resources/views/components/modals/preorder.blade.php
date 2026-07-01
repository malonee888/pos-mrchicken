<div class="modal-overlay" id="modal-preorder">
  <div class="modal">
    <div class="modal-header">
      <div><div class="modal-title">📋 Tambah Pre-Order</div><div class="modal-sub">Pesanan antrian saat slot penuh</div></div>
      <button class="btn-close" onclick="closeModal('modal-preorder')">✕</button>
    </div>

    <form id="form-preorder" method="POST" action="{{ route('preorder.store') }}">
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

        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Pelanggan *</label>
                <select name="customer_id" class="fselect" required>
                    <option value="">-- Pilih --</option>
                    @foreach($pelanggan as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fgroup">
                <label class="flabel">Slot Target</label>
                <select name="target_delivery_slot_id" class="fselect">
                    <option value="">-- Belum Ditentukan --</option>
                    @foreach($slot as $s)
                        <option value="{{ $s->id }}">{{ $s->name === 'Pagi' ? '🌅' : '🌆' }} {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="fgroup">
                <label class="flabel">Produk *</label>
                <select name="product_id" class="fselect" required>
                    <option value="">-- Pilih --</option>
                    @foreach($produk as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fgroup">
                <label class="flabel">Berat (KG) *</label>
                <input type="number" name="qty_kg" class="finput" placeholder="0.0" step="0.1" min="0.1" required>
            </div>
        </div>
        <div class="fgroup" style="margin-bottom:16px;">
            <label class="flabel">DP / Uang Muka (opsional)</label>
            <div class="finput-prefix">
                <span class="prefix">Rp</span>
                <input type="number" name="down_payment" class="finput" placeholder="0" min="0">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-preorder')">Batal</button>
            <button type="submit" class="btn btn-primary">💾 Simpan Pre-Order</button>
        </div>
    </form>
  </div>
</div>