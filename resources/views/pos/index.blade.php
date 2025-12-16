@extends('layouts.app')

@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale (POS)')

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
let cart = [];
let subtotal = 0;
let discount = 0;
let total = 0;

$(document).ready(function() {
    // Auto focus barcode input
    $('#barcodeInput').focus();
    
    // Barcode scanner
    $('#barcodeInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            let barcode = $(this).val().trim();
            if (barcode) {
                searchByBarcode(barcode);
                $(this).val('');
            }
        }
    });
    
    // Manual search
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        let query = $(this).val().trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(function() {
                searchByName(query);
            }, 500);
        } else {
            $('#searchResults').html('');
        }
    });
    
    // Payment method change
    $('#paymentMethod').on('change', function() {
        if ($(this).val() === 'cash') {
            $('#cashPaymentSection').show();
        } else {
            $('#cashPaymentSection').hide();
        }
    });
    
    // Calculate change
    $('#paymentAmount').on('keyup', function() {
        let paymentAmount = parseFloat($(this).val()) || 0;
        let change = paymentAmount - total;
        $('#changeAmount').text('Rp ' + formatNumber(Math.max(0, change)));
    });
    
    // Discount calculation
    $('#discountPercentage, #discountAmount').on('keyup', function() {
        calculateTotal();
    });
    
    // Clear cart
    $('#btnClear').on('click', function() {
        if (confirm('Yakin ingin membersihkan keranjang?')) {
            cart = [];
            updateCart();
        }
    });
    
    // Process payment
    $('#btnPay').on('click', function() {
        processPayment();
    });
});

function searchByBarcode(barcode) {
    $.ajax({
        url: '{{ route("products.search.barcode") }}',
        method: 'GET',
        data: {
            barcode: barcode,
            warehouse_id: $('#warehouseSelect').val(),
            price_tier_id: $('#priceTierSelect').val()
        },
        success: function(response) {
            if (response.success) {
                addToCart(response.data);
                $('#barcodeInput').focus();
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON.message || 'Produk tidak ditemukan');
            $('#barcodeInput').focus();
        }
    });
}

function searchByName(query) {
    $.ajax({
        url: '{{ route("products.search.name") }}',
        method: 'GET',
        data: {
            keyword: query, // ⬅️ SAMA DENGAN BACKEND
            warehouse_id: $('#warehouseSelect').val(),
            price_tier_id: $('#priceTierSelect').val()
        },
        success: function(products) {

            if (products.length > 0) {
                displaySearchResults(products);
            } else {
                $('#searchResults').html(
                    '<div class="list-group-item text-center text-muted">Produk tidak ditemukan</div>'
                );
            }
        },
        error: function() {
            $('#searchResults').html(
                '<div class="list-group-item text-danger">Gagal mengambil data</div>'
            );
        }
    });
}


function displaySearchResults(products) {
    let html = '';
    products.forEach(function(product) {
        html += `
            <a href="#" class="list-group-item list-group-item-action product-item"
               onclick='addToCart(${JSON.stringify(product)})'>
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>${product.name}</strong><br>
                        <small class="text-muted">
                            ${product.code} | Stok: ${product.stock}
                        </small>
                    </div>
                    <div class="text-end">
                        <strong class="text-primary">
                            Rp ${formatNumber(product.base_price)}
                        </strong>
                    </div>
                </div>
            </a>
        `;
    });
    $('#searchResults').html(html);
}

function addToCart(product) {
    let existingItem = cart.find(item => item.product_id === product.id);

    if (existingItem) {
        if (existingItem.qty < product.stock) {
            existingItem.qty++;
        } else {
            alert('Stok tidak mencukupi');
            return;
        }
    } else {
        cart.push({
            product_id: product.id,
            name: product.name,
            price: parseFloat(product.base_price),
            qty: 1,
            stock: product.stock
        });
    }

    updateCart();
    $('#searchInput').val('');
    $('#searchResults').html('');
}


function updateCart() {
    if (cart.length === 0) {
        $('#cartItems').html('<tr id="emptyCart"><td colspan="5" class="text-center text-muted py-4">Keranjang kosong</td></tr>');
    } else {
        let html = '';
        cart.forEach(function(item, index) {
            let itemSubtotal = item.qty * item.price;
            html += `
                <tr>
                    <td>
                        <small><strong>${item.name}</strong></small><br>
                        <small class="text-muted">@ Rp ${formatNumber(item.price)}</small>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <button class="btn btn-outline-secondary" onclick="updateQty(${index}, -1)">-</button>
                            <input type="number" class="form-control text-center" value="${item.qty}" 
                                   onchange="setQty(${index}, this.value)" min="1" max="${item.stock}">
                            <button class="btn btn-outline-secondary" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td class="text-end">
                        <small>Rp ${formatNumber(item.price)}</small>
                    </td>
                    <td class="text-end">
                        <strong>Rp ${formatNumber(itemSubtotal)}</strong>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="removeItem(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        $('#cartItems').html(html);
    }
    
    calculateTotal();
}

function updateQty(index, change) {
    let item = cart[index];
    let newQty = item.qty + change;
    
    if (newQty > 0 && newQty <= item.stock) {
        item.qty = newQty;
        updateCart();
    } else if (newQty > item.stock) {
        alert('Stok tidak mencukupi');
    }
}

function setQty(index, value) {
    let qty = parseInt(value);
    let item = cart[index];
    
    if (qty > 0 && qty <= item.stock) {
        item.qty = qty;
        updateCart();
    } else {
        alert('Jumlah tidak valid');
        updateCart();
    }
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCart();
}

function calculateTotal() {
    subtotal = 0;
    cart.forEach(function(item) {
        subtotal += item.qty * item.price;
    });
    
    // Calculate discount
    let discountPercentage = parseFloat($('#discountPercentage').val()) || 0;
    let discountAmount = parseFloat($('#discountAmount').val()) || 0;
    
    discount = discountAmount;
    if (discountPercentage > 0) {
        discount += (subtotal * discountPercentage) / 100;
    }
    
    total = subtotal - discount;
    
    $('#subtotalDisplay').text('Rp ' + formatNumber(subtotal));
    $('#discountDisplay').text('Rp ' + formatNumber(discount));
    $('#totalDisplay').text('Rp ' + formatNumber(total));
}

function processPayment() {
    if (cart.length === 0) {
        alert('Keranjang kosong');
        return;
    }
    
    let paymentMethod = $('#paymentMethod').val();
    let paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
    
    if (paymentMethod === 'cash' && paymentAmount < total) {
        alert('Jumlah pembayaran tidak cukup');
        return;
    }
    
    if (!confirm('Proses pembayaran?')) {
        return;
    }
    
    let data = {
        _token: '{{ csrf_token() }}',
        items: cart,
        price_tier_id: $('#priceTierSelect').val(),
        warehouse_id: $('#warehouseSelect').val(),
        discount_percentage: parseFloat($('#discountPercentage').val()) || 0,
        discount_amount: parseFloat($('#discountAmount').val()) || 0,
        voucher_code: $('#voucherCode').val(),
        payment_method: paymentMethod,
        payment_amount: paymentMethod === 'cash' ? paymentAmount : total
    };
    
    $.ajax({
        url: '{{ route("transactions.store") }}',
        method: 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                alert('Transaksi berhasil!\nNomor: ' + response.data.transaction_number);
                
                // Open print receipt in new window
                window.open('{{ url("/transactions") }}/' + response.data.transaction_id + '/print', '_blank');
                
                // Reset form
                cart = [];
                updateCart();
                $('#voucherCode').val('');
                $('#discountPercentage').val('');
                $('#discountAmount').val('');
                $('#paymentAmount').val('');
                $('#barcodeInput').focus();
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON.message || 'Terjadi kesalahan');
        }
    });
}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}
</script>
@endpush
