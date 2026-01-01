@extends('layouts.app')

@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale (POS)')
@section('body-class', 'page-pos')

@push('styles')
<style>
    .product-item {
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .product-item:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
    }
    
    .cart-table {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .total-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }
    
    .total-amount {
        font-size: 32px;
        font-weight: bold;
        color: #28a745;
    }
    
    .barcode-input {
        font-size: 18px;
        padding: 12px;
    }
    
    .payment-btn {
        font-size: 18px;
        padding: 15px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Left Panel - Product Selection -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-barcode"></i> Scan / Cari Produk</h5>
            </div>
            <div class="card-body">
                <!-- Warehouse & Price Tier Selection -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Gudang</label>
                       <select class="form-select" id="warehouseSelect">
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">
                                {{ $warehouse->name }} | {{ $warehouse->address }} | {{ $warehouse->phone }}
                            </option>
                        @endforeach
                    </select>
                    </div>
                    <div class="col-md-6">
                        <label>Tipe Harga</label>
                        <select class="form-select" id="priceTierSelect">
                            @foreach($priceTiers as $tier)
                            <option value="{{ $tier->id }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $tier->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Barcode Scanner -->
                <div class="mb-3">
                    <label>Scan Barcode</label>
                    <input type="text" class="form-control barcode-input" id="barcodeInput" 
                           placeholder="Scan barcode disini..." autofocus>
                </div>
                
                <!-- Manual Search -->
                <div class="mb-3">
                    <label>Cari Manual</label>
                    <input type="text" class="form-control" id="searchInput" 
                           placeholder="Ketik nama atau kode produk...">
                </div>
                
                <!-- Search Results -->
                <div id="searchResults" class="list-group"></div>
            </div>
        </div>
    </div>
    
    <!-- Right Panel - Cart & Payment -->
    <div class="col-md-5">
        <!-- Cart -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h5>
            </div>
            <div class="card-body p-0">
                <div class="cart-table">
                    <table class="table table-sm mb-0" id="cartTable">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th width="80">Qty</th>
                                <th width="100">Harga</th>
                                <th width="100">Subtotal</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="cartItems">
                            <tr id="emptyCart">
                                <td colspan="5" class="text-center text-muted py-4">
                                    Keranjang kosong
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Discount & Total -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6">
                        <small>Subtotal:</small>
                    </div>
                    <div class="col-6 text-end">
                        <strong id="subtotalDisplay">Rp 0</strong>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-12">
                        <input type="text" class="form-control form-control-sm" 
                               id="voucherCode" placeholder="Kode voucher (opsional)">
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm" 
                               id="discountPercentage" placeholder="Diskon %" min="0" max="100">
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm" 
                               id="discountAmount" placeholder="Diskon Rp" min="0">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <small>Diskon:</small>
                    </div>
                    <div class="col-6 text-end">
                        <strong class="text-danger" id="discountDisplay">Rp 0</strong>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-6">
                        <h5>TOTAL:</h5>
                    </div>
                    <div class="col-6 text-end">
                        <h4 class="total-amount mb-0" id="totalDisplay">Rp 0</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment -->
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label>Metode Pembayaran</label>
                    <select class="form-select" id="paymentMethod">
                        <option value="cash">Tunai</option>
                        <option value="card">Kartu Debit/Kredit</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
                
                <div class="mb-3" id="cashPaymentSection">
                    <label>Jumlah Bayar</label>
                    <input type="number" class="form-control" id="paymentAmount" 
                           placeholder="0" min="0">
                    <small class="text-muted">Kembalian: <strong id="changeAmount">Rp 0</strong></small>
                </div>
                
                <button class="btn btn-primary w-100 payment-btn" id="btnPay">
                    <i class="fas fa-check-circle"></i> Bayar
                </button>
                
                <button class="btn btn-danger w-100 mt-2" id="btnClear">
                    <i class="fas fa-times-circle"></i> Bersihkan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
     window.POS_ROUTES = {
        searchBarcode: "{{ route('products.search.barcode') }}",
        searchName: "{{ route('products.search.name') }}",
        storeTransaction: "{{ route('transactions.store') }}"
    };
</script>
@endpush
