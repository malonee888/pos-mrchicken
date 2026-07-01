<?php

namespace App\Http\Controllers;

use App\Models\PreOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\DeliverySlot;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreOrderController extends Controller
{
    public function index()
    {
        $preOrder = PreOrder::with(['customer', 'product', 'targetSlot'])
            ->where('status', 'menunggu')
            ->orderBy('queue_position')
            ->get();

        $pelanggan = Customer::where('is_active', true)->orderBy('name')->get();
        $produk = Product::where('is_active', true)->orderBy('name')->get();
        $slot = DeliverySlot::where('is_active', true)->orderBy('start_time')->get();

        return view('preorder.index', compact('preOrder', 'pelanggan', 'produk', 'slot'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'qty_kg' => 'required|numeric|min:0.1',
            'down_payment' => 'nullable|numeric|min:0',
            'target_delivery_slot_id' => 'nullable|exists:delivery_slots,id',
        ]);

        // Generate kode PO otomatis, sama polanya seperti kode transaksi
        $urutanTerakhir = PreOrder::count() + 1;
        $kodePo = 'PO-' . str_pad($urutanTerakhir, 3, '0', STR_PAD_LEFT);

        // Posisi antrian = jumlah pre-order yang masih 'menunggu' + 1
        $posisiAntrian = PreOrder::where('status', 'menunggu')->count() + 1;

        PreOrder::create([
            'po_code' => $kodePo,
            'customer_id' => $validated['customer_id'],
            'product_id' => $validated['product_id'],
            'qty_kg' => $validated['qty_kg'],
            'down_payment' => $validated['down_payment'] ?? null,
            'target_delivery_slot_id' => $validated['target_delivery_slot_id'] ?? null,
            'queue_position' => $posisiAntrian,
            'status' => 'menunggu',
        ]);

        return redirect()->route('preorder.index')->with('success', 'Pre-order berhasil dicatat, masuk antrian #' . $posisiAntrian . '.');
    }

    public function alokasikan(PreOrder $preOrder)
    {
        if ($preOrder->status !== 'menunggu') {
            return redirect()->route('preorder.index')->with('error', 'Pre-order ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($preOrder) {

            $produk = $preOrder->product;
            $subtotal = $produk->price_per_kg * $preOrder->qty_kg;

            // Generate kode transaksi otomatis, sama persis pola di TransactionController
            $urutanTerakhir = Transaction::count() + 1;
            $kodeTransaksi = 'TRX-' . str_pad($urutanTerakhir, 3, '0', STR_PAD_LEFT);

            $transaksi = Transaction::create([
                'transaction_code' => $kodeTransaksi,
                'customer_id' => $preOrder->customer_id,
                'delivery_slot_id' => $preOrder->target_delivery_slot_id,
                'user_id' => auth()->id(),
                'total_kg' => $preOrder->qty_kg,
                'total_price' => $subtotal,
                'payment_method' => $preOrder->down_payment > 0 ? 'dp' : 'hutang',
                'down_payment' => $preOrder->down_payment,
                'notes' => 'Dialokasikan dari Pre-Order ' . $preOrder->po_code,
                'delivery_status' => 'proses',
                'transaction_date' => today(),
            ]);

            $transaksi->items()->create([
                'product_id' => $produk->id,
                'qty_kg' => $preOrder->qty_kg,
                'price_per_kg' => $produk->price_per_kg,
                'subtotal' => $subtotal,
            ]);

            // Kalau metode bukan lunas (selalu hutang/dp di sini), catat hutangnya
            \App\Models\Debt::create([
                'transaction_id' => $transaksi->id,
                'customer_id' => $preOrder->customer_id,
                'initial_amount' => $subtotal,
                'paid_amount' => $preOrder->down_payment ?? 0,
                'status' => ($preOrder->down_payment ?? 0) > 0 ? 'cicilan' : 'belum_lunas',
            ]);

            // Tambahkan ke kapasitas slot kalau ada slot target
            if ($preOrder->target_delivery_slot_id) {
                $kapasitasHariIni = $preOrder->targetSlot->kapasitasHariIni();
                $kapasitasHariIni->increment('used_kg', $preOrder->qty_kg);
            }

            // Update pre-order: tandai dialokasikan, hubungkan ke transaksi yang baru dibuat
            $preOrder->update([
                'status' => 'dialokasikan',
                'transaction_id' => $transaksi->id,
            ]);

            // Geser posisi antrian semua pre-order yang masih menunggu, di belakang yang ini
            PreOrder::where('status', 'menunggu')
                ->where('queue_position', '>', $preOrder->queue_position)
                ->decrement('queue_position');
        });

        return redirect()->route('preorder.index')->with('success', 'Pre-order berhasil dialokasikan menjadi transaksi.');
    }

    public function batalkan(PreOrder $preOrder)
    {
        $preOrder->update(['status' => 'batal']);

        // Geser posisi antrian yang di belakangnya, sama seperti saat dialokasikan
        PreOrder::where('status', 'menunggu')
            ->where('queue_position', '>', $preOrder->queue_position)
            ->decrement('queue_position');

        return redirect()->route('preorder.index')->with('success', 'Pre-order berhasil dibatalkan.');
    }
}