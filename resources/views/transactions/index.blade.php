@extends('layouts.app')

@section('title', 'Transaksi')
@section('page-title', 'Daftar Transaksi')

@push('styles')
<style>
    .table thead th {
        background-color: #007bff;
        color: #fff;
    }
    .action-btn {
        margin-right: 5px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-receipt"></i> Transaksi</h5>
            </div>
            <div class="card-body">

                <!-- Filter -->
                <form class="row mb-3" method="GET" action="{{ route('transactions.index') }}">
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="Dari Tanggal">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="Sampai Tanggal">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="payment_method">
                            <option value="">Semua Metode</option>
                            <option value="cash" {{ request('payment_method')=='cash' ? 'selected' : '' }}>Tunai</option>
                            <option value="card" {{ request('payment_method')=='card' ? 'selected' : '' }}>Kartu</option>
                            <option value="ewallet" {{ request('payment_method')=='ewallet' ? 'selected' : '' }}>E-Wallet</option>
                            <option value="transfer" {{ request('payment_method')=='transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success w-100" type="submit"><i class="fas fa-filter"></i> Filter</button>
                    </div>
                </form>

                <!-- Transactions Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nomor Transaksi</th>
                                <th>Tanggal</th>
                                <th>User</th>
                                <th>Warehouse</th>
                                <th>Total</th>
                                <th>Metode Bayar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $index => $transaction)
                                <tr>
                                    <td>{{ $transactions->firstItem() + $index }}</td>
                                    <td>{{ $transaction->transaction_number }}</td>
                                    <td>{{ $transaction->transaction_date->format('d-m-Y H:i') }}</td>
                                    <td>{{ $transaction->user->name ?? '-' }}</td>
                                    <td>{{ $transaction->warehouse->name ?? '-' }}</td>
                                    <td>Rp {{ number_format($transaction->total,0,',','.') }}</td>
                                    <td>{{ ucfirst($transaction->payment_method) }}</td>
                                    <td>
                                        @if($transaction->status == 'completed')
                                            <span class="badge bg-success">Selesai</span>
                                        @elseif($transaction->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($transaction->status == 'cancelled')
                                            <span class="badge bg-danger">Batal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-sm btn-primary action-btn">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <a href="{{ url('/transactions/'.$transaction->id.'/print') }}" target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-print"></i> Cetak
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Tidak ada transaksi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $transactions->withQueryString()->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
