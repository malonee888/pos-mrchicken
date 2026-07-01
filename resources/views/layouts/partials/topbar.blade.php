<div class="topbar">
  <div>
    <div class="page-title">@yield('title', 'Dashboard')</div>
    <div class="page-sub">@yield('subtitle', 'Selamat datang kembali!')</div>
  </div>
  <div class="topbar-right">
    <div class="topbar-time" id="topbar-clock">--:--:--</div>
    @if(auth()->user()->role === 'owner')
      <span class="topbar-badge badge-owner">👑 Owner</span>
    @else
      <span class="topbar-badge badge-karyawan">👤 Karyawan</span>
    @endif
  </div>
</div>
