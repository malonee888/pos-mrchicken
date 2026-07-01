@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('subtitle', 'Rekap performa penjualan MR. CHICKEN')

@section('content')

<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card orange">
        <div class="stat-icon">💵</div>
        <div class="stat-label">Total Penjualan Periode Ini</div>
        <div class="stat-value">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</div>
        <div class="stat-sub">{{ $jumlahTransaksi }} transaksi · {{ number_format($totalKgTerjual, 0) }} KG</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon">📊</div>
        <div class="stat-label">Rata-rata per Transaksi</div>
        <div class="stat-value">Rp {{ number_format($rataRataPerTransaksi, 0, ',', '.') }}</div>
        <div class="stat-sub">Dari {{ $jumlahTransaksi }} transaksi</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">🍗</div>
        <div class="stat-label">Produk Terlaris Periode Ini</div>
        <div class="stat-value" style="font-size:1.1rem;">{{ $rekapProduk->first()?->product?->name ?? '-' }}</div>
        <div class="stat-sub">{{ number_format($rekapProduk->first()?->total_kg ?? 0, 0) }} KG terjual</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">📈 Laporan Penjualan Harian</div>
        <form method="GET" action="{{ route('laporan.index') }}" class="flex-gap">
            <input type="date" name="tanggal_mulai" class="finput" style="width:auto;" value="{{ $tanggalMulai }}">
            <span>s/d</span>
            <input type="date" name="tanggal_selesai" class="finput" style="width:auto;" value="{{ $tanggalSelesai }}" max="{{ today()->format('Y-m-d') }}">
            <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tanggal</th><th>Total Transaksi</th><th>Total KG Terjual</th><th>Total Pemasukan</th><th>Hutang Baru</th><th>Net Kas</th></tr></thead>
            <tbody>
                @forelse($rekapHarian as $hari)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($hari['tanggal'])->translatedFormat('d M Y') }}</td>
                        <td>{{ $hari['jumlah_transaksi'] }}</td>
                        <td>{{ number_format($hari['total_kg'], 0) }} KG</td>
                        <td class="font-mono fw-700 text-orange">Rp {{ number_format($hari['total_penjualan'], 0, ',', '.') }}</td>
                        <td class="font-mono text-red">Rp {{ number_format($hari['hutang_baru'], 0, ',', '.') }}</td>
                        <td class="font-mono fw-700 text-green">Rp {{ number_format($hari['net_kas'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; color: var(--ink-2); padding: 24px;">
                            Tidak ada transaksi di periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($rekapHarian->count() > 0)
                <tfoot>
                    <tr style="background:var(--orange-bg);font-weight:700;">
                        <td>TOTAL ({{ $rekapHarian->count() }} hari)</td>
                        <td>{{ $jumlahTransaksi }}</td>
                        <td>{{ number_format($totalKgTerjual, 0) }} KG</td>
                        <td class="font-mono fw-700 text-orange">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
                        <td class="font-mono text-red">Rp {{ number_format($rekapHarian->sum('hutang_baru'), 0, ',', '.') }}</td>
                        <td class="font-mono fw-700 text-green">Rp {{ number_format($rekapHarian->sum('net_kas'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="grid-2" style="margin-top:24px;">
    <div class="card mb-0">
        <div class="card-header"><div class="card-title">🍗 Rekap Penjualan per Produk</div></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Produk</th><th>Total KG</th><th>Total Nilai</th></tr></thead>
                <tbody>
                    @forelse($rekapProduk as $rp)
                        <tr>
                            <td>{{ $rp->product->name ?? '-' }}</td>
                            <td>{{ number_format($rp->total_kg, 0) }} KG</td>
                            <td class="font-mono">Rp {{ number_format($rp->total_nilai, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center; color: var(--ink-2); padding: 20px;">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-0">
        <div class="card-header"><div class="card-title">💳 Rekap Metode Pembayaran</div></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Metode</th><th>Jumlah Transaksi</th></tr></thead>
                <tbody>
                    @forelse($rekapPembayaran as $metode => $jumlah)
                        @php
                            $labelMetode = match($metode) {
                                'lunas' => '✅ Lunas',
                                'hutang' => '📒 Hutang',
                                'dp' => '💳 DP',
                                default => $metode,
                            };
                        @endphp
                        <tr>
                            <td>{{ $labelMetode }}</td>
                            <td>{{ $jumlah }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="text-align:center; color: var(--ink-2); padding: 20px;">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection