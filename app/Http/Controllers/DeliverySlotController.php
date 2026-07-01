<?php

namespace App\Http\Controllers;

use App\Models\DeliverySlot;
use Illuminate\Http\Request;

class DeliverySlotController extends Controller
{
    public function index()
    {
        $slot = DeliverySlot::where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->map(function ($s) {
                $kapasitasHariIni = $s->kapasitasHariIni();
                $s->terisi_kg = $kapasitasHariIni->used_kg;

                if ($s->terisi_kg <= $s->normal_threshold_kg) {
                    $s->status_label = 'Normal';
                    $s->status_kelas = 'status-normal';
                } elseif ($s->terisi_kg <= $s->almost_full_threshold_kg) {
                    $s->status_label = 'Hampir Penuh';
                    $s->status_kelas = 'status-hampir';
                } else {
                    $s->status_label = 'Overload';
                    $s->status_kelas = 'status-overload';
                }

                return $s;
            });

        // Daftar transaksi hari ini yang sudah pilih slot, untuk tabel "Pengiriman Hari Ini"
        $pengirimanHariIni = \App\Models\Transaction::with(['customer', 'deliverySlot'])
            ->whereNotNull('delivery_slot_id')
            ->whereDate('transaction_date', today())
            ->orderBy('delivery_slot_id')
            ->get();

        return view('pengiriman.index', compact('slot', 'pengirimanHariIni'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Hanya Owner yang bisa mengatur slot pengiriman.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'max_capacity_kg' => 'required|numeric|min:1',
            'normal_threshold_kg' => 'required|numeric|min:0|lt:max_capacity_kg',
            'almost_full_threshold_kg' => 'required|numeric|gt:normal_threshold_kg|lte:max_capacity_kg',
        ]);

        DeliverySlot::create($validated);

        return redirect()->route('pengiriman.index')->with('success', 'Slot pengiriman berhasil ditambahkan.');
    }

    public function edit(DeliverySlot $deliverySlot)
    {
        return response()->json($deliverySlot);
    }

    public function update(Request $request, DeliverySlot $deliverySlot)
    {
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Hanya Owner yang bisa mengatur slot pengiriman.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'max_capacity_kg' => 'required|numeric|min:1',
            'normal_threshold_kg' => 'required|numeric|min:0|lt:max_capacity_kg',
            'almost_full_threshold_kg' => 'required|numeric|gt:normal_threshold_kg|lte:max_capacity_kg',
        ]);

        $deliverySlot->update($validated);

        return redirect()->route('pengiriman.index')->with('success', 'Slot pengiriman berhasil diperbarui.');
    }
}