@extends('layouts.app')

@section('title', 'Pre-Order & Antrian')
@section('subtitle', 'Kelola pesanan antrian saat slot pengiriman penuh')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <span class="alert-icon">✅</span>
        <div class="alert-body">{{ session('success') }}</div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <span class="alert-icon">⚠️</span>
        <div class="alert-body">{{ session('error') }}</div>
    </div>
@endif

@if($preOrder->count() > 0)
    <div class="alert alert-warning" style="margin-bottom: 20px;">
        <span class="alert-icon">⚠️</span>
        <div class="alert-body">
            <div class="alert-title">{{ $preOrder->count() }} Pre-Order Menunggu Alokasi Slot</div>
            Segera alokasikan ke slot yang tersedia agar pesanan bisa diproses.
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div class="card-title">📋 Daftar Pre-Order & Antrian</div>
        <button class="btn btn-primary btn-sm" onclick="bukaModalPreorder()">＋ Tambah Pre-Order</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID PO</th><th>Pelanggan</th><th>Pesanan</th><th>Total KG</th><th>DP / Uang Muka</th><th>Slot Target</th><th>Posisi Antrian</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($preOrder as $po)
                    <tr>
                        <td class="font-mono">{{ $po->po_code }}</td>
                        <td>
                            <div class="fw-700">{{ $po->customer->name ?? '-' }}</div>
                            <div class="text-muted">{{ $po->customer->phone ?? '-' }}</div>
                        </td>
                        <td>{{ $po->product->name ?? '-' }} × {{ number_format($po->qty_kg, 0) }}kg</td>
                        <td>{{ number_format($po->qty_kg, 0) }} KG</td>
                        <td class="font-mono">{{ $po->down_payment ? 'Rp ' . number_format($po->down_payment, 0, ',', '.') : '-' }}</td>
                        <td>
                            @if($po->targetSlot)
                                <span class="badge badge-amber">{{ $po->targetSlot->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><span style="font-weight:800;color:var(--orange);font-size:1.1rem;">#{{ $po->queue_position }}</span></td>
                        <td><span class="badge badge-amber">Menunggu</span></td>
                        <td>
                            <div class="flex-gap">
                                <form method="POST" action="{{ route('preorder.alokasikan', $po->id) }}" onsubmit="return confirm('Alokasikan {{ $po->po_code }} menjadi transaksi sekarang?')">
                                    @csrf
                                    <input type="hidden" name="_method" value="PATCH">
                                    <button type="submit" class="btn btn-success btn-sm">Alokasi →</button>
                                </form>
                                <form method="POST" action="{{ route('preorder.batalkan', $po->id) }}" onsubmit="return confirm('Batalkan pre-order {{ $po->po_code }}?')">
                                    @csrf
                                    <input type="hidden" name="_method" value="PATCH">
                                    <button type="submit" class="btn btn-secondary btn-sm btn-icon" title="Batalkan">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color: var(--ink-2); padding: 24px;">
                            Tidak ada pre-order yang sedang menunggu. Antrian kosong.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('components.modals.preorder')

@endsection

@section('scripts')
<script>
    function bukaModalPreorder() {
        document.getElementById('form-preorder').reset();
        openModal('modal-preorder');
    }
</script>
@endsection
