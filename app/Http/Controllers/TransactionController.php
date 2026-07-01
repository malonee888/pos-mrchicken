<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transaksi = Transaction::with(['customer', 'items.product'])
            ->latest('created_at')
            ->get();

        $pelanggan = Customer::where('is_active', true)->orderBy('name')->get();
        $produk = Product::where('is_active', true)->orderBy('name')->get();
        $slot = \App\Models\DeliverySlot::where('is_active', true)->orderBy('start_time')->get();

        // Disiapkan di sini (bukan di Blade) supaya data sudah bersih saat dikirim ke JavaScript
        $produkUntukJs = $produk->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'harga' => $p->price_per_kg,
                'unit' => $p->unit,
            ];
        })->values();

        return view('transaksi.index', compact('transaksi', 'pelanggan', 'produk', 'produkUntukJs', 'slot'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_slot_id' => 'nullable|exists:delivery_slots,id',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'qty_kg' => 'required|array|min:1',
            'qty_kg.*' => 'required|numeric|min:0.1',
            'payment_method' => 'required|in:lunas,hutang,dp',
            'down_payment' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // ── VALIDASI STOK SEBELUM SIMPAN (di luar DB::transaction, supaya bisa redirect dengan pesan jelas) ──
        $stokKurang = [];
        foreach ($validated['product_id'] as $index => $productId) {
            $produk = \App\Models\Product::find($productId);
            $qtyDiminta = $validated['qty_kg'][$index];

            $totalMasuk = \App\Models\StockMovement::where('product_id', $productId)->where('type', 'masuk')->sum('quantity_kg');
            $totalKeluar = \App\Models\StockMovement::where('product_id', $productId)->where('type', 'keluar')->sum('quantity_kg');
            $stokTersedia = $totalMasuk - $totalKeluar;

            if ($qtyDiminta > $stokTersedia) {
                $stokKurang[] = "{$produk->name} (diminta {$qtyDiminta} KG, tersisa {$stokTersedia} KG)";
            }
        }

        if (count($stokKurang) > 0) {
            return back()->withErrors([
                'stok' => 'Stok tidak cukup untuk: ' . implode(', ', $stokKurang) . '. Kurangi jumlah pesanan, atau catat sisanya lewat menu Pre-Order.',
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $request) {

            // ── 1. Generate kode transaksi otomatis (TRX-001, TRX-002, dst) ──
            $urutanTerakhir = Transaction::count() + 1;
            $kodeTransaksi = 'TRX-' . str_pad($urutanTerakhir, 3, '0', STR_PAD_LEFT);

            // ── 2. Hitung total dari semua baris produk ──
            $totalKg = 0;
            $totalHarga = 0;
            $itemSiapSimpan = [];

            foreach ($validated['product_id'] as $index => $productId) {
                $produk = Product::find($productId);
                $qty = $validated['qty_kg'][$index];
                $subtotal = $produk->price_per_kg * $qty;

                $totalKg += $qty;
                $totalHarga += $subtotal;

                $itemSiapSimpan[] = [
                    'product_id' => $productId,
                    'qty_kg' => $qty,
                    'price_per_kg' => $produk->price_per_kg, // snapshot harga saat ini
                    'subtotal' => $subtotal,
                ];
            }

            // ── 3. Simpan transaksi induk ──
            $transaksi = Transaction::create([
                'transaction_code' => $kodeTransaksi,
                'customer_id' => $validated['customer_id'],
                'delivery_slot_id' => $validated['delivery_slot_id'] ?? null,
                'user_id' => auth()->id(),
                'total_kg' => $totalKg,
                'total_price' => $totalHarga,
                'payment_method' => $validated['payment_method'],
                'down_payment' => $validated['down_payment'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'delivery_status' => 'proses',
                'transaction_date' => today(),
            ]);

            // ── 3b. Kalau pilih slot, tambahkan ke kapasitas terpakai hari ini ──
            if (!empty($validated['delivery_slot_id'])) {
                $slotDipilih = \App\Models\DeliverySlot::find($validated['delivery_slot_id']);
                $kapasitasHariIni = $slotDipilih->kapasitasHariIni();
                $kapasitasHariIni->increment('used_kg', $totalKg);
            }

            // ── 4. Simpan semua baris produk ──
            foreach ($itemSiapSimpan as $item) {
                $transaksi->items()->create($item);
            }

            // ── 5. Kalau metode bayar BUKAN lunas, otomatis buat record hutang ──
            if ($validated['payment_method'] !== 'lunas') {
                $sudahDibayar = $validated['payment_method'] === 'dp'
                    ? ($validated['down_payment'] ?? 0)
                    : 0;

                Debt::create([
                    'transaction_id' => $transaksi->id,
                    'customer_id' => $validated['customer_id'],
                    'initial_amount' => $totalHarga,
                    'paid_amount' => $sudahDibayar,
                    'status' => $sudahDibayar > 0 ? 'cicilan' : 'belum_lunas',
                ]);
            }
        });

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil disimpan.');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['customer', 'items.product', 'user']);

        return response()->json($transaction);
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'delivery_status' => 'required|in:proses,dalam_perjalanan,terkirim,selesai,batal',
        ]);

        DB::transaction(function () use ($validated, $transaction) {

            $statusSebelumnya = $transaction->delivery_status;

            $transaction->update($validated);

            // ── Kurangi stok HANYA saat transisi MENUJU 'selesai', dan HANYA sekali ──
            // Pengecekan $statusSebelumnya !== 'selesai' mencegah stok berkurang dobel
            // jika tombol diklik berulang atau status di-set ulang.
            if ($validated['delivery_status'] === 'selesai' && $statusSebelumnya !== 'selesai') {
                foreach ($transaction->items as $item) {
                    \App\Models\StockMovement::create([
                        'product_id' => $item->product_id,
                        'type' => 'keluar',
                        'quantity_kg' => $item->qty_kg,
                        'reference_type' => 'transaction',
                        'reference_id' => $transaction->id,
                        'note' => 'Transaksi ' . $transaction->transaction_code . ' selesai',
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('transaksi.index')->with('success', 'Status pengiriman berhasil diperbarui.');
    }
}