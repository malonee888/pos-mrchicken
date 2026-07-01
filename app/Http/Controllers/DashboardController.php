<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ── 1. STAT CARDS ──
        // Catatan: Schema::hasTable() mengecek apakah tabel sudah ada.
        // Ini hanya diperlukan SELAMA tabel transactions/products/dll belum dibuat
        // (Tahap 8-15). Setelah semua tabel ada, kondisi ini akan selalu TRUE
        // dan query akan berjalan normal seperti seharusnya.

        $totalPenjualanHariIni = Schema::hasTable('transactions')
            ? \App\Models\Transaction::whereDate('transaction_date', today())->sum('total_price')
            : 0;

        $totalKgTerjualHariIni = Schema::hasTable('transaction_items')
            ? \App\Models\TransactionItem::whereHas('transaction', function ($q) {
                $q->whereDate('transaction_date', today());
            })->sum('qty_kg')
            : 0;

        $jumlahTransaksiHariIni = Schema::hasTable('transactions')
            ? \App\Models\Transaction::whereDate('transaction_date', today())->count()
            : 0;

        $totalHutangBelumLunas = Schema::hasTable('debts')
            ? \App\Models\Debt::where('status', '!=', 'lunas')->sum('initial_amount')
                - \App\Models\Debt::where('status', '!=', 'lunas')->sum('paid_amount')
            : 0;

        $jumlahPelangganHutang = Schema::hasTable('debts')
            ? \App\Models\Debt::where('status', '!=', 'lunas')->distinct('customer_id')->count('customer_id')
            : 0;

        // ── STOK TERSEDIA (total semua produk) ──
        $totalStokTersedia = 0;
        $jumlahProdukAktif = 0;
        if (Schema::hasTable('stock_movements') && Schema::hasTable('products')) {
            $totalMasuk = \App\Models\StockMovement::where('type', 'masuk')->sum('quantity_kg');
            $totalKeluar = \App\Models\StockMovement::where('type', 'keluar')->sum('quantity_kg');
            $totalStokTersedia = $totalMasuk - $totalKeluar;
            $jumlahProdukAktif = \App\Models\Product::where('is_active', true)->count();
        }

        // ── TREND DIBANDING KEMARIN ──
        $penjualanKemarin = Schema::hasTable('transactions')
            ? \App\Models\Transaction::whereDate('transaction_date', today()->subDay())->sum('total_price')
            : 0;

        $trendPenjualan = $penjualanKemarin > 0
            ? round((($totalPenjualanHariIni - $penjualanKemarin) / $penjualanKemarin) * 100, 1)
            : 0;

        $kgKemarin = Schema::hasTable('transaction_items')
            ? \App\Models\TransactionItem::whereHas('transaction', function ($q) {
                $q->whereDate('transaction_date', today()->subDay());
            })->sum('qty_kg')
            : 0;

        $trendKg = $kgKemarin > 0
            ? round((($totalKgTerjualHariIni - $kgKemarin) / $kgKemarin) * 100, 1)
            : 0;

        // ── 2. GRAFIK PENJUALAN (toggle: minggu / bulan) ──
        $periode = request('periode', 'minggu'); // default: minggu, bisa diganti ?periode=bulan di URL

        if ($periode === 'bulan') {
            $jumlahHari = today()->daysInMonth; // otomatis 28/29/30/31 sesuai bulan berjalan
            $awalPeriode = today()->startOfMonth();
        } else {
            $jumlahHari = 7;
            $awalPeriode = today()->subDays(6);
        }

        $grafikPenjualan = [];
        for ($i = 0; $i < $jumlahHari; $i++) {
            $tanggal = $awalPeriode->copy()->addDays($i);

            if ($tanggal->gt(today())) break; // jangan tampilkan tanggal di masa depan

            $totalHari = Schema::hasTable('transactions')
                ? \App\Models\Transaction::whereDate('transaction_date', $tanggal)->sum('total_price')
                : 0;

            $grafikPenjualan[] = [
                'label' => $periode === 'bulan' ? $tanggal->format('d') : $tanggal->translatedFormat('D'),
                'tanggal' => $tanggal->format('Y-m-d'),
                'total' => (float) $totalHari,
            ];
        }

        $nilaiTertinggi = max(array_column($grafikPenjualan, 'total')) ?: 1; // hindari bagi 0
        $nilaiTerendah = min(array_column($grafikPenjualan, 'total'));

        // Hitung tren: bandingkan rata-rata paruh kedua vs paruh pertama periode
        $titikTengah = (int) (count($grafikPenjualan) / 2);
        $rataAwal = $titikTengah > 0 ? array_sum(array_column(array_slice($grafikPenjualan, 0, $titikTengah), 'total')) / $titikTengah : 0;
        $rataAkhir = $titikTengah > 0 ? array_sum(array_column(array_slice($grafikPenjualan, $titikTengah), 'total')) / (count($grafikPenjualan) - $titikTengah) : 0;
        $trenNaik = $rataAkhir >= $rataAwal;

        

        // ── 3. STATUS SLOT PENGIRIMAN HARI INI ──
        $statusSlot = collect();
        if (Schema::hasTable('delivery_slots') && Schema::hasTable('daily_slot_capacities')) {
            $statusSlot = \App\Models\DeliverySlot::where('is_active', true)
                ->get()
                ->map(function ($slot) {
                    $kapasitasHariIni = \App\Models\DailySlotCapacity::where('delivery_slot_id', $slot->id)
                        ->where('date', today())
                        ->first();

                    $terisi = $kapasitasHariIni->used_kg ?? 0;

                    if ($terisi <= $slot->normal_threshold_kg) {
                        $status = 'Normal';
                    } elseif ($terisi <= $slot->almost_full_threshold_kg) {
                        $status = 'Hampir Penuh';
                    } else {
                        $status = 'Overload';
                    }

                    return [
                        'nama' => $slot->name,
                        'terisi' => $terisi,
                        'kapasitas' => $slot->max_capacity_kg,
                        'status' => $status,
                    ];
                });
        }

        // ── 4. TOP PRODUK (berdasarkan total KG terjual bulan ini) ──
        $topProduk = collect();
        if (Schema::hasTable('transaction_items') && Schema::hasTable('products')) {
            $topProduk = \App\Models\TransactionItem::query()
                ->whereHas('transaction', function ($q) {
                    $q->whereMonth('transaction_date', today()->month)
                      ->whereYear('transaction_date', today()->year);
                })
                ->selectRaw('product_id, SUM(qty_kg) as total_kg')
                ->groupBy('product_id')
                ->orderByDesc('total_kg')
                ->with('product')
                ->take(5)
                ->get();
        }

        // ── 5. TRANSAKSI TERBARU (5 terakhir) ──
        $transaksiTerbaru = collect();
        if (Schema::hasTable('transactions')) {
            $transaksiTerbaru = \App\Models\Transaction::with('customer')
                ->latest('created_at')
                ->take(5)
                ->get();
        }

        // ── 6. BADGE PRE-ORDER MENUNGGU (untuk sidebar) ──
        $jumlahPreorderMenunggu = Schema::hasTable('pre_orders')
            ? \App\Models\PreOrder::where('status', 'menunggu')->count()
            : 0;

        return view('dashboard.index', compact(
            'totalPenjualanHariIni',
            'totalKgTerjualHariIni',
            'jumlahTransaksiHariIni',
            'totalHutangBelumLunas',
            'jumlahPelangganHutang',
            'totalStokTersedia',
            'jumlahProdukAktif',
            'trendPenjualan',
            'trendKg',
            'grafikPenjualan',
            'nilaiTertinggi',
            'nilaiTerendah',
            'trenNaik',
            'periode',
            'statusSlot',
            'topProduk',
            'transaksiTerbaru',
            'jumlahPreorderMenunggu'
        ));
    }
}