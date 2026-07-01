<!-- TOAST CONTAINER -->
<div class="toast-container" id="toast-container"></div>

<script>
  // ── JAM REAL-TIME (murni tampilan, tidak menyentuh data) ──
  function updateClock() {
    const now = new Date();
    const clockEl = document.getElementById('topbar-clock');
    if (clockEl) {
      clockEl.textContent = now.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
    }
  }
  setInterval(updateClock, 1000);
  updateClock();

  // ── MODAL (murni tampilan, tidak menyentuh data) ──
  function openModal(id) { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
  });

  // ── TOAST (notifikasi popup, murni tampilan) ──
  function showToast(msg, type = 'success') {
    const el = document.createElement('div');
    el.className = 'toast toast-' + type;
    el.innerHTML = (type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️') + ' ' + msg;
    document.getElementById('toast-container').appendChild(el);
    setTimeout(() => el.remove(), 3500);
  }
</script>