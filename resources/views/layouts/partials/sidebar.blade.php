<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logo">🐔</div>
    <div class="sidebar-brand-text">
      <div class="sidebar-brand-name">MR. CHICKEN</div>
      <div class="sidebar-brand-tag">POS SYSTEM v1.0</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Utama</div>

    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <span class="nav-icon">📊</span> Dashboard
    </a>

    <a href="{{ route('transaksi.index') }}" class="nav-item {{ request()->routeIs('transaksi.*') ? 'active' : '' }}">
      <span class="nav-icon">🛒</span> Pesan Ayam
      @if(($jumlahPreorderMenunggu ?? 0) > 0)
        <span class="nav-badge" id="badge-preorder">{{ $jumlahPreorderMenunggu }}</span>
      @endif
    </a>

    <a href="{{ route('pengiriman.index') }}" class="nav-item {{ request()->routeIs('pengiriman.*') ? 'active' : '' }}">
      <span class="nav-icon">🚚</span> Pengiriman & Slot
    </a>

    <a href="{{ route('preorder.index') }}" class="nav-item {{ request()->routeIs('preorder.*') ? 'active' : '' }}">
      <span class="nav-icon">📋</span> Pre-Order & Antrian
    </a>

    <div class="nav-section-label">Data Master</div>

    <a href="{{ route('pelanggan.index') }}" class="nav-item {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
      <span class="nav-icon">👥</span> Pelanggan
    </a>

    <a href="{{ route('produk.index') }}" class="nav-item {{ request()->routeIs('produk.*') ? 'active' : '' }}">
      <span class="nav-icon">🍗</span> Produk
    </a>

    <a href="{{ route('stok.index') }}" class="nav-item {{ request()->routeIs('stok.*') ? 'active' : '' }}">
      <span class="nav-icon">📦</span> Stok Produk
    </a>

    <a href="{{ route('hutang.index') }}" class="nav-item {{ request()->routeIs('hutang.*') ? 'active' : '' }}">
      <span class="nav-icon">💰</span> Buku Hutang
    </a>

    @if(auth()->user()->role === 'owner')
      <div class="nav-section-label">Laporan & Admin</div>

      <a href="{{ route('laporan.index') }}" class="nav-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
        <span class="nav-icon">📈</span> Laporan Penjualan
      </a>

      <a href="{{ route('pengguna.index') }}" class="nav-item {{ request()->routeIs('pengguna.*') ? 'active' : '' }}">
        <span class="nav-icon">⚙️</span> Kelola Pengguna
      </a>
    @endif
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
      <div>
        <div class="user-name">{{ auth()->user()->name }}</div>
        <div class="user-role">{{ auth()->user()->role === 'owner' ? 'Full Access' : 'Akses Terbatas' }}</div>
      </div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn-logout">🚪 Keluar dari Sistem</button>
    </form>
  </div>
</aside>
