<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Default: laporan bulan berjalan, dari tanggal 1 sampai hari ini
        $tanggalMulai = $request->input('tanggal_mulai', today()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->input('tanggal_selesai', today()->format('Y-m-d'));

        $transaksiPeriode = Transaction::with(['customer', 'items.product'])
            ->whereBetween('transaction_date', [$tanggalMulai, $tanggalSelesai])
            ->where('delivery_status', '!=', 'batal')
            ->get();

        $totalPenjualan = $transaksiPeriode->sum('total_price');
        $totalKgTerjual = $transaksiPeriode->sum('total_kg');
        $jumlahTransaksi = $transaksiPeriode->count();
        $rataRataPerTransaksi = $jumlahTransaksi > 0 ? $totalPenjualan / $jumlahTransaksi : 0;

        // Hutang baru yang tercipta per tanggal transaksi (untuk hitung Net Kas)
        $hutangPerTanggal = \App\Models\Debt::whereHas('transaction', function ($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->whereBetween('transaction_date', [$tanggalMulai, $tanggalSelesai]);
            })
            ->with('transaction')
            ->get()
            ->groupBy(fn($debt) => $debt->transaction->transaction_date->format('Y-m-d'))
            ->map(fn($grup) => $grup->sum('initial_amount'));

        // Rekap harian, untuk tabel breakdown per tanggal
        $rekapHarian = $transaksiPeriode
            ->groupBy(fn($trx) => $trx->transaction_date->format('Y-m-d'))
            ->map(function ($grup, $tanggal) use ($hutangPerTanggal) {
                $totalPenjualan = $grup->sum('total_price');
                $hutangBaru = $hutangPerTanggal->get($tanggal, 0);

                return [
                    'tanggal' => $tanggal,
                    'jumlah_transaksi' => $grup->count(),
                    'total_kg' => $grup->sum('total_kg'),
                    'total_penjualan' => $totalPenjualan,
                    'hutang_baru' => $hutangBaru,
                    'net_kas' => $totalPenjualan - $hutangBaru,
                ];
            })
            ->sortByDesc('tanggal')
            ->values();

        // Rekap per produk, untuk tahu produk apa yang paling laku di periode ini
        $rekapProduk = TransactionItem::whereHas('transaction', function ($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->whereBetween('transaction_date', [$tanggalMulai, $tanggalSelesai])
                  ->where('delivery_status', '!=', 'batal');
            })
            ->selectRaw('product_id, SUM(qty_kg) as total_kg, SUM(subtotal) as total_nilai')
            ->groupBy('product_id')
            ->orderByDesc('total_nilai')
            ->with('product')
            ->get();

        // Rekap metode pembayaran
        $rekapPembayaran = $transaksiPeriode->groupBy('payment_method')->map->count();

        return view('laporan.index', compact(
            'tanggalMulai',
            'tanggalSelesai',
            'totalPenjualan',
            'totalKgTerjual',
            'jumlahTransaksi',
            'rataRataPerTransaksi',
            'rekapHarian',
            'rekapProduk',
            'rekapPembayaran'
        ));
    }
}