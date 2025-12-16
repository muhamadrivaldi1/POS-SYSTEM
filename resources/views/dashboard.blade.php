@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Today's Sales -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6 class="card-title">Penjualan Hari Ini</h6>
                <h3 class="mb-0">Rp {{ number_format($todaySales, 0, ',', '.') }}</h3>
                <small><i class="fas fa-calendar-day"></i> {{ date('d M Y') }}</small>
            </div>
        </div>
    </div>
    
    <!-- Today's Transactions -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6 class="card-title">Transaksi Hari Ini</h6>
                <h3 class="mb-0">{{ $todayTransactions }}</h3>
                <small><i class="fas fa-receipt"></i> Transaksi</small>
            </div>
        </div>
    </div>
    
    @if(auth()->user()->isAdmin() || auth()->user()->isSupervisor())
    <!-- Monthly Sales -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h6 class="card-title">Penjualan Bulan Ini</h6>
                <h3 class="mb-0">Rp {{ number_format($monthSales, 0, ',', '.') }}</h3>
                <small><i class="fas fa-calendar-alt"></i> {{ date('F Y') }}</small>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h6 class="card-title">Stok Menipis</h6>
                <h3 class="mb-0">{{ $lowStockProducts }}</h3>
                <small><i class="fas fa-exclamation-triangle"></i> Produk</small>
            </div>
        </div>
    </div>
    @else
    <!-- My Sales Today (for kasir) -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h6 class="card-title">Transaksi Saya</h6>
                <h3 class="mb-0">{{ $mySales }}</h3>
                <small><i class="fas fa-user"></i> Hari Ini</small>
            </div>
        </div>
    </div>
    
    <!-- Cash Session Status -->
    <div class="col-md-3 mb-4">
        <div class="card text-white {{ $openSession ? 'bg-success' : 'bg-secondary' }}">
            <div class="card-body">
                <h6 class="card-title">Status Kas</h6>
                <h3 class="mb-0">{{ $openSession ? 'Terbuka' : 'Tertutup' }}</h3>
                @if($openSession)
                <small><i class="fas fa-check-circle"></i> Sejak {{ $openSession->opened_at->format('H:i') }}</small>
                @else
                <small><i class="fas fa-times-circle"></i> Buka sesi kas</small>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@if(auth()->user()->isAdmin() || auth()->user()->isSupervisor())
<!-- Top Products Today -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Produk Terlaris Hari Ini</h5>
            </div>
            <div class="card-body">
                @if($topProducts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Produk</th>
                                <th>Jumlah Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $index => $product)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $product->name }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $product->total_qty }} unit</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-muted mb-0">Belum ada transaksi hari ini</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('pos.index') }}" class="btn btn-primary me-2 mb-2">
                    <i class="fas fa-shopping-cart"></i> Buka POS
                </a>
                <a href="{{ route('transactions.index') }}" class="btn btn-info me-2 mb-2">
                    <i class="fas fa-receipt"></i> Lihat Transaksi
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->isSupervisor())
                <a href="{{ route('products.index') }}" class="btn btn-success me-2 mb-2">
                    <i class="fas fa-box"></i> Kelola Produk
                </a>
                <a href="{{ route('reports.sales') }}" class="btn btn-warning me-2 mb-2">
                    <i class="fas fa-chart-line"></i> Laporan
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
