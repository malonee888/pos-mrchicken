<div class="modal-overlay" id="modal-struk">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header">
      <div><div class="modal-title">🧾 Struk Penjualan</div></div>
      <button class="btn-close" onclick="closeModal('modal-struk')">✕</button>
    </div>
    <div id="isi-struk" style="font-family:'JetBrains Mono',monospace;font-size:.82rem;background:var(--surface);border:1px dashed var(--border);border-radius:var(--radius-sm);padding:20px;line-height:1.8;">
        {{-- Konten struk diisi otomatis lewat JavaScript (lihatStruk) saat tombol 🧾 diklik --}}
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-struk')">Tutup</button>
      <button class="btn btn-primary" onclick="showToast('Struk dicetak','success')">🖨️ Cetak</button>
    </div>
  </div>
</div>