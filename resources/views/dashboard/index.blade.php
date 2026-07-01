@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Selamat datang kembali!')

@section('content')

{{-- ══════ STAT CARDS ══════ --}}
<div class="stats-grid">
    <div class="stat-card orange">
        <div class="stat-icon">💵</div>
        <div class="stat-label">Total Penjualan Hari Ini</div>
        <div class="stat-value">Rp {{ number_format($totalPenjualanHariIni, 0, ',', '.') }}</div>
        <div class="stat-sub">
            @if($trendPenjualan >= 0)
                <span class="trend-up stat-trend">↑ +{{ $trendPenjualan }}%</span>
            @else
                <span class="trend-down stat-trend">↓ {{ $trendPenjualan }}%</span>
            @endif
            vs kemarin
        </div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon">⚖️</div>
        <div class="stat-label">Total KG Terjual</div>
        <div class="stat-value">{{ number_format($totalKgTerjualHariIni, 0, ',', '.') }} KG</div>
        <div class="stat-sub">
            @if($trendKg >= 0)
                <span class="trend-up stat-trend">↑ +{{ $trendKg }}%</span>
            @else
                <span class="trend-down stat-trend">↓ {{ $trendKg }}%</span>
            @endif
            vs kemarin
        </div>
    </div>

    <div class="stat-card blue">
        <div class="stat-icon">🧾</div>
        <div class="stat-label">Total Transaksi</div>
        <div class="stat-value">{{ $jumlahTransaksiHariIni }}</div>
        <div class="stat-sub">Hari ini</div>
    </div>

    <div class="stat-card red">
        <div class="stat-icon">📒</div>
        <div class="stat-label">Total Hutang Aktif</div>
        <div class="stat-value">Rp {{ number_format($totalHutangBelumLunas, 0, ',', '.') }}</div>
        <div class="stat-sub">{{ $jumlahPelangganHutang }} pelanggan</div>
    </div>

    <div class="stat-card amber">
        <div class="stat-icon">📦</div>
        <div class="stat-label">Stok Tersedia</div>
        <div class="stat-value">{{ number_format($totalStokTersedia, 0, ',', '.') }} KG</div>
        <div class="stat-sub">{{ $jumlahProdukAktif }} jenis produk aktif</div>
    </div>
</div>

{{-- ══════ SLOT STATUS ══════ --}}
<div class="slot-grid">
    @forelse($statusSlot as $slot)
        @php
            $persenTerisi = $slot['kapasitas'] > 0 ? round(($slot['terisi'] / $slot['kapasitas']) * 100) : 0;
            $kelasFill = match($slot['status']) {
                'Normal' => 'fill-normal',
                'Hampir Penuh' => 'fill-hampir',
                default => 'fill-overload',
            };
            $kelasBadge = match($slot['status']) {
                'Normal' => 'status-normal',
                'Hampir Penuh' => 'status-hampir',
                default => 'status-overload',
            };
            $emoji = $slot['nama'] === 'Pagi' ? '🌅' : ($slot['nama'] === 'Sore' ? '🌆' : '🚚');
        @endphp
        <div class="slot-card">
            <div class="slot-header">
                <div>
                    <div class="slot-title">{{ $emoji }} Slot {{ $slot['nama'] }}</div>
                </div>
                <span class="slot-status-badge {{ $kelasBadge }}">{{ $slot['status'] }}</span>
            </div>
            <div class="slot-bar-track"><div class="slot-bar-fill {{ $kelasFill }}" style="width:{{ $persenTerisi }}%"></div></div>
            <div class="slot-kg"><span>{{ $slot['terisi'] }} / {{ $slot['kapasitas'] }} KG terisi</span><span>{{ $persenTerisi }}%</span></div>
        </div>
    @empty
        <div class="card">
            <p style="color: var(--ink-2); text-align:center;">Belum ada data slot pengiriman. Akan tersedia setelah Tahap 13 selesai.</p>
        </div>
    @endforelse
</div>

<div class="grid-2">
    {{-- ══════ CHART PENJUALAN (LINE CHART) ══════ --}}
    <div class="card mb-0">
        <div class="card-header">
            <div>
                <div class="card-title">📈 Grafik Penjualan {{ $periode === 'bulan' ? 'Bulanan' : 'Mingguan' }}</div>
                <div class="card-subtitle">
                    Total Rp {{ number_format(array_sum(array_column($grafikPenjualan, 'total')), 0, ',', '.') }}
                    {{ $periode === 'bulan' ? 'bulan ini' : 'minggu ini' }}
                    <span style="color: {{ $trenNaik ? 'var(--green)' : 'var(--red)' }}; font-weight:700; margin-left:6px;">
                        {{ $trenNaik ? '↑ Naik' : '↓ Turun' }}
                    </span>
                </div>
            </div>
            <div class="flex-gap">
                <a href="{{ route('dashboard', ['periode' => 'minggu']) }}" class="btn btn-sm {{ $periode === 'minggu' ? 'btn-primary' : 'btn-secondary' }}">7 Hari</a>
                <a href="{{ route('dashboard', ['periode' => 'bulan']) }}" class="btn btn-sm {{ $periode === 'bulan' ? 'btn-primary' : 'btn-secondary' }}">Bulan Ini</a>
            </div>
        </div>

        @php
            $lebarSvg = 700;
            $tinggiSvg = 180;
            $padding = 30;
            $jumlahTitik = count($grafikPenjualan);
            $rentang = ($nilaiTertinggi - $nilaiTerendah) ?: 1;

            $titikKoordinat = [];
            foreach ($grafikPenjualan as $idx => $hari) {
                $x = $jumlahTitik > 1
                    ? $padding + ($idx / ($jumlahTitik - 1)) * ($lebarSvg - 2 * $padding)
                    : $lebarSvg / 2;
                $y = $tinggiSvg - $padding - (($hari['total'] - $nilaiTerendah) / $rentang) * ($tinggiSvg - 2 * $padding);
                $titikKoordinat[] = ['x' => round($x, 1), 'y' => round($y, 1), 'data' => $hari];
            }

            $pathLine = collect($titikKoordinat)->map(fn($t) => "{$t['x']},{$t['y']}")->implode(' ');
            $pathArea = $pathLine . " {$titikKoordinat[count($titikKoordinat)-1]['x']},{$tinggiSvg} {$titikKoordinat[0]['x']},{$tinggiSvg}";
        @endphp

        <div class="chart-area" style="height:auto;">
            <svg viewBox="0 0 {{ $lebarSvg }} {{ $tinggiSvg }}" style="width:100%; height:200px; overflow:visible;">
                <defs>
                    <linearGradient id="areaFade" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="var(--orange)" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="var(--orange)" stop-opacity="0"/>
                    </linearGradient>
                </defs>

                {{-- Area gradasi di bawah garis --}}
                <polygon points="{{ $pathArea }}" fill="url(#areaFade)" />

                {{-- Garis utama --}}
                <polyline points="{{ $pathLine }}" fill="none" stroke="var(--orange)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />

                {{-- Titik-titik data --}}
                @foreach($titikKoordinat as $t)
                    <circle cx="{{ $t['x'] }}" cy="{{ $t['y'] }}" r="3.5" fill="var(--orange)" stroke="white" stroke-width="1.5">
                        <title>{{ $t['data']['label'] }}: Rp {{ number_format($t['data']['total'], 0, ',', '.') }}</title>
                    </circle>
                @endforeach
            </svg>

            <div class="chart-bars" style="margin-top:8px;">
                @foreach($grafikPenjualan as $idx => $hari)
                    @if($periode === 'minggu' || $idx % 5 === 0 || $idx === count($grafikPenjualan) - 1)
                        <div class="chart-bar-wrap" style="flex:1;"><div class="chart-label">{{ $hari['label'] }}</div></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════ TOP PRODUK ══════ --}}
    <div class="card mb-0">
        <div class="card-header">
            <div>
                <div class="card-title">🏆 Produk Terlaris</div>
                <div class="card-subtitle">Top 5 produk bulan ini</div>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;">
            @php $warnaUrutan = ['var(--orange)', 'var(--amber)', 'var(--blue)', 'var(--green)', 'var(--ink-3)']; @endphp
            @forelse($topProduk as $index => $item)
                @php
                    $warna = $warnaUrutan[$index % count($warnaUrutan)];
                    $nilaiMax = $topProduk->max('total_kg') ?: 1;
                    $persenBar = round(($item->total_kg / $nilaiMax) * 100);
                @endphp
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="font-size:.75rem;font-weight:700;color:var(--ink-3);width:20px;">{{ $index + 1 }}</span>
                    <div style="flex:1">
                        <div style="font-size:.88rem;font-weight:600;">{{ $item->product->name ?? 'Produk Dihapus' }}</div>
                        <div style="background:var(--surface);border-radius:99px;height:6px;margin-top:4px;overflow:hidden;">
                            <div style="background:{{ $warna }};height:100%;border-radius:99px;width:{{ $persenBar }}%"></div>
                        </div>
                    </div>
                    <span class="font-mono" style="font-size:.82rem;font-weight:700;color:{{ $warna }};">{{ number_format($item->total_kg, 0) }} KG</span>
                </div>
            @empty
                <p style="color: var(--ink-2); text-align:center;">Belum ada data penjualan produk.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ══════ TRANSAKSI TERBARU ══════ --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">🧾 Transaksi Terbaru</div>
        <a href="{{ route('transaksi.index') }}" class="btn btn-secondary btn-sm">Lihat Semua →</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>ID</th><th>Pelanggan</th><th>Total KG</th><th>Total Harga</th><th>Pembayaran</th><th>Status</th><th>Waktu</th>
            </tr></thead>
            <tbody>
                @forelse($transaksiTerbaru as $trx)
                    @php
                        $kelasBayar = match($trx->payment_method) {
                            'lunas' => 'badge-green',
                            'hutang' => 'badge-red',
                            default => 'badge-amber',
                        };
                        $labelBayar = match($trx->payment_method) {
                            'lunas' => '✓ Lunas',
                            'hutang' => 'Hutang',
                            default => 'DP',
                        };
                        $kelasStatus = match($trx->delivery_status) {
                            'selesai' => 'badge-green',
                            'terkirim', 'dalam_perjalanan' => 'badge-blue',
                            'batal' => 'badge-red',
                            default => 'badge-amber',
                        };
                    @endphp
                    <tr>
                        <td class="font-mono">{{ $trx->transaction_code }}</td>
                        <td>{{ $trx->customer->name ?? '-' }}</td>
                        <td>{{ number_format($trx->total_kg, 0) }} KG</td>
                        <td class="font-mono fw-700">Rp {{ number_format($trx->total_price, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $kelasBayar }}">{{ $labelBayar }}</span></td>
                        <td><span class="badge {{ $kelasStatus }}">{{ ucfirst(str_replace('_', ' ', $trx->delivery_status)) }}</span></td>
                        <td>{{ $trx->created_at->format('H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color: var(--ink-2); padding: 24px;">
                            Belum ada transaksi. Data akan muncul di sini setelah Tahap 10 selesai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection