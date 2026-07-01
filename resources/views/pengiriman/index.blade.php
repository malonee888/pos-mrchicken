@extends('layouts.app')

@section('title', 'Pengiriman & Slot')
@section('subtitle', 'Pantau kapasitas slot dan jadwal pengiriman hari ini')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <span class="alert-icon">✅</span>
        <div class="alert-body">{{ session('success') }}</div>
    </div>
@endif

<div class="slot-grid mb-24" style="margin-bottom:24px">
    @forelse($slot as $s)
        @php
            $sisaKg = $s->max_capacity_kg - $s->terisi_kg;
            $persenTerisi = $s->max_capacity_kg > 0 ? round(($s->terisi_kg / $s->max_capacity_kg) * 100) : 0;
            $emoji = $s->name === 'Pagi' ? '🌅' : ($s->name === 'Sore' ? '🌆' : '🚚');
            $kelasFill = match($s->status_label) {
                'Normal' => 'fill-normal',
                'Hampir Penuh' => 'fill-hampir',
                default => 'fill-overload',
            };
        @endphp
        <div class="slot-card">
            <div class="slot-header">
                <div>
                    <div class="slot-title">{{ $emoji }} Slot {{ $s->name }}</div>
                    <div class="slot-time">{{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}</div>
                </div>
                <span class="slot-status-badge {{ $s->status_kelas }}">{{ $s->status_label }}</span>
            </div>
            <div class="slot-bar-track"><div class="slot-bar-fill {{ $kelasFill }}" style="width:{{ $persenTerisi }}%"></div></div>
            <div class="slot-kg mb-16"><span>{{ number_format($s->terisi_kg, 0) }} / {{ number_format($s->max_capacity_kg, 0) }} KG terisi</span><span class="fw-700">{{ number_format($sisaKg, 0) }} KG sisa</span></div>
            <div class="flex-gap">
                <span class="text-muted">Batas kapasitas:</span>
                <span class="badge badge-green">≤{{ number_format($s->normal_threshold_kg, 0) }}kg Normal</span>
                <span class="badge badge-amber">{{ number_format($s->normal_threshold_kg + 1, 0) }}-{{ number_format($s->almost_full_threshold_kg, 0) }}kg Hampir</span>
                <span class="badge badge-red">{{ number_format($s->almost_full_threshold_kg + 1, 0) }}-{{ number_format($s->max_capacity_kg, 0) }}kg Overload</span>
            </div>
            @if(auth()->user()->role === 'owner')
                <div class="flex-gap" style="margin-top:12px;">
                    <button class="btn btn-secondary btn-sm" onclick="bukaModalEditSlot({{ $s->id }})">⚙️ Atur Slot</button>
                </div>
            @endif
        </div>
    @empty
        <div class="card mb-0" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
            <p style="color: var(--ink-2);">Belum ada slot pengiriman dikonfigurasi.</p>
        </div>
    @endforelse
</div>

@if(auth()->user()->role === 'owner')
    <div class="flex-between mb-24">
        <div></div>
        <button class="btn btn-secondary btn-sm" onclick="bukaModalTambahSlot()">＋ Tambah Slot Baru</button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div class="card-title">🚚 Daftar Pengiriman Hari Ini</div>
        <div class="card-subtitle">{{ $pengirimanHariIni->count() }} pesanan terjadwal</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Slot</th><th>Pelanggan</th><th>Total KG</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($pengirimanHariIni as $trx)
                    @php
                        $kelasSlot = $trx->deliverySlot->name === 'Pagi' ? 'badge-blue' : 'badge-amber';
                        $kelasStatus = match($trx->delivery_status) {
                            'selesai' => 'badge-green',
                            'terkirim', 'dalam_perjalanan' => 'badge-blue',
                            'batal' => 'badge-red',
                            default => 'badge-amber',
                        };
                    @endphp
                    <tr>
                        <td class="font-mono">{{ $trx->transaction_code }}</td>
                        <td><span class="badge {{ $kelasSlot }}">{{ $trx->deliverySlot->name ?? '-' }}</span></td>
                        <td>{{ $trx->customer->name ?? '-' }}</td>
                        <td>{{ number_format($trx->total_kg, 1) }} KG</td>
                        <td><span class="badge {{ $kelasStatus }}">{{ ucfirst(str_replace('_', ' ', $trx->delivery_status)) }}</span></td>
                        <td>
                            <a href="{{ route('transaksi.index') }}" class="btn btn-secondary btn-sm btn-icon" title="Kelola di halaman Transaksi">👁</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; color: var(--ink-2); padding: 24px;">
                            Belum ada pengiriman terjadwal untuk hari ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('components.modals.slot')

@endsection

@section('scripts')
<script>
    function bukaModalTambahSlot() {
        document.querySelector('#modal-slot .modal-title').textContent = '⚙️ Tambah Slot Pengiriman';
        document.getElementById('form-slot').action = "{{ route('pengiriman.store') }}";
        document.getElementById('form-slot-method').value = 'POST';
        document.getElementById('form-slot').reset();
        openModal('modal-slot');
    }

    function bukaModalEditSlot(id) {
        fetch(`/pengiriman/${id}/edit`)
            .then(res => res.json())
            .then(data => {
                document.querySelector('#modal-slot .modal-title').textContent = '⚙️ Atur Slot Pengiriman';
                document.getElementById('form-slot').action = `/pengiriman/${id}`;
                document.getElementById('form-slot-method').value = 'PUT';
                document.querySelector('#form-slot input[name=name]').value = data.name;
                document.querySelector('#form-slot input[name=start_time]').value = data.start_time;
                document.querySelector('#form-slot input[name=end_time]').value = data.end_time;
                document.querySelector('#form-slot input[name=max_capacity_kg]').value = data.max_capacity_kg;
                document.querySelector('#form-slot input[name=normal_threshold_kg]').value = data.normal_threshold_kg;
                document.querySelector('#form-slot input[name=almost_full_threshold_kg]').value = data.almost_full_threshold_kg;
                openModal('modal-slot');
            });
    }
</script>
@endsection