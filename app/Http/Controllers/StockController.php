<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $produk = Product::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                $totalMasuk = StockMovement::where('product_id', $p->id)->where('type', 'masuk')->sum('quantity_kg');
                $totalKeluar = StockMovement::where('product_id', $p->id)->where('type', 'keluar')->sum('quantity_kg');
                $p->stok_saat_ini = $totalMasuk - $totalKeluar;

                $updateTerakhir = StockMovement::where('product_id', $p->id)->latest('created_at')->first();
                $p->update_terakhir = $updateTerakhir?->created_at;

                return $p;
            });

        $riwayat = StockMovement::with(['product', 'user'])
            ->latest('created_at')
            ->take(20)
            ->get();

        $produkStokRendah = $produk->filter(fn($p) => $p->stok_saat_ini < 50);

        return view('stok.index', compact('produk', 'riwayat', 'produkStokRendah'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_kg' => 'required|numeric|min:0.1',
            'note' => 'nullable|string|max:255',
        ]);

        StockMovement::create([
            'product_id' => $validated['product_id'],
            'type' => 'masuk',
            'quantity_kg' => $validated['quantity_kg'],
            'note' => $validated['note'] ?? 'Stok masuk manual',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('stok.index')->with('success', 'Stok berhasil ditambahkan.');
    }
}