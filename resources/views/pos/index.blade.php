@extends('layouts.app')

@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale (POS)')
@section('body-class', 'page-pos')

@push('styles')
<style>
body.page-pos { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
.product-item { cursor: pointer; transition: all 0.2s; }
.product-item:hover { background-color: #e2e6ea; transform: translateY(-2px); }
.cart-table { max-height: 400px; overflow-y: auto; }
.total-section { background-color: #fff; padding: 15px; border-radius: 8px; }
.total-amount { font-size: 28px; font-weight: bold; color: #28a745; }
.barcode-input { font-size: 16px; padding: 10px; }
.payment-btn { font-size: 16px; padding: 12px; }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Left Panel -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-primary text-white"><i class="fas fa-barcode"></i> Scan / Cari Produk</div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label>Gudang</label>
                        <select class="form-select" id="warehouseSelect">
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Tipe Harga</label>
                        <select class="form-select" id="priceTierSelect">
                            @foreach($priceTiers as $tier)
                            <option value="{{ $tier->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $tier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label>Scan Barcode</label>
                    <input type="text" class="form-control barcode-input" id="barcodeInput" placeholder="Scan barcode..." autofocus>
                </div>

                <div class="mb-2">
                    <label>Cari Produk</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Ketik nama atau kode produk...">
                </div>

                <div id="searchResults" class="list-group"></div>
            </div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="col-md-5">
        <div class="card mb-2">
            <div class="card-header bg-success text-white"><i class="fas fa-shopping-cart"></i> Keranjang</div>
            <div class="card-body p-0">
                <div class="cart-table">
                    <table class="table table-sm mb-0" id="cartTable">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th width="70">Qty</th>
                                <th width="100">Harga</th>
                                <th width="100">Subtotal</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="cartItems">
                            <tr><td colspan="5" class="text-center text-muted py-4">Keranjang kosong</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-2 total-section">
            <div class="row mb-2">
                <div class="col-6"><small>Subtotal</small></div>
                <div class="col-6 text-end"><strong id="subtotalDisplay">Rp 0</strong></div>
            </div>

            <div class="row mb-2">
                <div class="col-12 mb-1">
                    <input type="text" class="form-control form-control-sm" id="voucherCode" placeholder="Kode voucher (opsional)">
                </div>
                <div class="col-6 mb-1">
                    <input type="number" class="form-control form-control-sm" id="discountPercentage" placeholder="Diskon %" min="0" max="100">
                </div>
                <div class="col-6 mb-1">
                    <input type="number" class="form-control form-control-sm" id="discountAmount" placeholder="Diskon Rp" min="0">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6"><small>Diskon</small></div>
                <div class="col-6 text-end text-danger"><strong id="discountDisplay">Rp 0</strong></div>
            </div>
            <hr>
            <div class="row mb-2">
                <div class="col-6"><h5>Total</h5></div>
                <div class="col-6 text-end"><h4 class="total-amount mb-0" id="totalDisplay">Rp 0</h4></div>
            </div>
        </div>

        <div class="card mb-2">
            <div class="card-body">
                <label>Metode Pembayaran</label>
                <select class="form-select mb-2" id="paymentMethod">
                    <option value="cash">Tunai</option>
                    <option value="card">Kartu</option>
                    <option value="ewallet">E-Wallet</option>
                    <option value="transfer">Transfer Bank</option>
                </select>

                <div class="mb-2" id="cashPaymentSection">
                    <label>Jumlah Bayar</label>
                    <input type="number" class="form-control" id="paymentAmount" min="0">
                    <small>Kembalian: <strong id="changeAmount">Rp 0</strong></small>
                </div>

                <button class="btn btn-primary w-100 payment-btn" id="btnPay"><i class="fas fa-check-circle"></i> Bayar</button>
                <button class="btn btn-danger w-100 mt-2 payment-btn" id="btnClear"><i class="fas fa-times-circle"></i> Bersihkan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];
let productsCache = [];

// Format angka jadi Rp 100.000
function formatNumber(num){
    return new Intl.NumberFormat('id-ID').format(num);
}

// ======================= CART UPDATE =======================
function updateCart(){
    let tbody = $('#cartItems');
    if(cart.length === 0){
        tbody.html('<tr><td colspan="5" class="text-center text-muted py-4">Keranjang kosong</td></tr>');
    } else {
        let html = '';
        cart.forEach((item,i)=>{
            html += `<tr>
                        <td><strong>${item.name}</strong><br><small class="text-muted">Rp ${formatNumber(item.price)}</small></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" onclick="changeQuantity(${i},-1)">-</button>
                                <input type="number" class="form-control text-center" value="${item.quantity}" min="1" max="${item.stock}" onchange="setQuantity(${i}, this.value)">
                                <button class="btn btn-outline-secondary" onclick="changeQuantity(${i},1)">+</button>
                            </div>
                        </td>
                        <td class="text-end">Rp ${formatNumber(item.price)}</td>
                        <td class="text-end">Rp ${formatNumber(item.quantity*item.price)}</td>
                        <td><button class="btn btn-sm btn-danger" onclick="removeItem(${i})"><i class="fas fa-trash"></i></button></td>
                     </tr>`;
        });
        tbody.html(html);
    }
    calculateTotals();
}

// ======================= QUANTITY HANDLING =======================
function changeQuantity(i, delta){
    let item = cart[i];
    let newQty = item.quantity + delta;
    if(newQty > 0 && newQty <= item.stock){ item.quantity = newQty; updateCart(); }
    else if(newQty>item.stock) alert('Stok tidak mencukupi');
}

function setQuantity(i, val){
    let quantity = parseInt(val);
    if(quantity > 0 && quantity <= cart[i].stock){ cart[i].quantity = quantity; updateCart(); }
    else { alert('Jumlah tidak valid'); updateCart(); }
}

function removeItem(i){ cart.splice(i,1); updateCart(); }

// ======================= TOTAL & DISKON =======================
function calculateTotals(){
    let subtotal = cart.reduce((sum,item)=>sum + item.quantity*item.price,0);
    let discPerc = parseFloat($('#discountPercentage').val())||0;
    let discAmt = parseFloat($('#discountAmount').val())||0;
    let discount = discAmt + subtotal*discPerc/100;
    if(discount > subtotal) discount = subtotal; // diskon tidak boleh lebih dari subtotal
    let total = subtotal - discount;

    $('#subtotalDisplay').text('Rp '+formatNumber(subtotal));
    $('#discountDisplay').text('Rp '+formatNumber(discount));
    $('#totalDisplay').text('Rp '+formatNumber(total));

    calculateChange();
}

function calculateChange(){
    let total = parseFloat($('#totalDisplay').text().replace(/\D/g,''))||0;
    let payment = parseFloat($('#paymentAmount').val())||0;
    $('#changeAmount').text('Rp '+formatNumber(Math.max(0, payment - total)));
}

// ======================= DOCUMENT READY =======================
$(function(){
    $('#barcodeInput').focus();

    $('#barcodeInput').keypress(function(e){
        if(e.which === 13){
            e.preventDefault();
            let barcode = $(this).val().trim();
            if(barcode){ searchByBarcode(barcode); $(this).val(''); }
        }
    });

    $('#searchInput').on('keyup', function(){
        let q = $(this).val().trim();
        if(q.length>=2){ searchByName(q); } else { $('#searchResults').html(''); }
    });

    $('#discountPercentage,#discountAmount,#paymentAmount').on('input', calculateTotals);

    $('#paymentMethod').on('change', function(){
        $('#cashPaymentSection').toggle($(this).val() === 'cash');
    });

    $('#btnClear').click(()=>{ if(confirm('Bersihkan keranjang?')){ cart=[]; updateCart(); } });
    $('#btnPay').click(processPayment);
});

// ======================= SEARCH / ADD PRODUCT =======================
function searchByBarcode(barcode){
    $.get('{{ route("pos.products.search.barcode") }}', { 
        barcode, 
        warehouse_id: $('#warehouseSelect').val(), 
        price_tier_id: $('#priceTierSelect').val() 
    }, function(res){
        if(res.success) addToCart(res.data);
        else alert('Produk tidak ditemukan');
    });
}

function searchByName(keyword){
    $.get("{{ route('pos.products.search.name') }}", {
        keyword: keyword,
        warehouse_id: $('#warehouseSelect').val()
    }, function(res){
        if(!res.success || res.data.length === 0){
            $('#searchResults').html('<div class="list-group-item text-muted">Produk tidak ditemukan</div>');
            return;
        }

        productsCache = res.data;
        let html = '';
        res.data.forEach((p, index) => {
            let price = parseFloat(p.base_price.toString().replace(/[^\d.-]/g,'')) || 0;
            html += `
            <a href="#" class="list-group-item product-item mb-2" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="product-name fw-semibold">${p.name}</div>
                        <div class="product-price text-muted">Rp ${formatNumber(price)}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge ${p.stock > 0 ? 'bg-success' : 'bg-danger'} badge-stock">
                            Stok: ${p.stock}
                        </span>
                    </div>
                </div>
            </a>`;
        });

        $('#searchResults').html(html);

        $('.product-item').click(function(e){
            e.preventDefault();
            let index = $(this).data('index');
            addToCart(productsCache[index]);
            $('#searchResults').html('');
            $('#searchInput').val('');
        });
    });
}

// ======================= ADD TO CART =======================
function addToCart(product){
    let item = cart.find(i=>i.product_id===product.id);
    let price = parseFloat(product.base_price.toString().replace(/[^\d.-]/g,'')) || 0;
    let stock = parseInt(product.stock)||0;

    if(item){
        if(item.quantity < stock) item.quantity++;
        else alert('Stok tidak mencukupi');
    } else {
        if(stock > 0){
            cart.push({ product_id: product.id, name: product.name, price: price, quantity: 1, stock: stock });
        } else {
            alert('Stok habis');
        }
    }
    updateCart();
}

// ======================= PAYMENT =======================

function processPayment(){
    if(cart.length===0){ alert('Keranjang kosong'); return; }

    let paymentMethod = $('#paymentMethod').val();
    let total = parseFloat($('#totalDisplay').text().replace(/\D/g,''))||0;
    let payment = parseFloat($('#paymentAmount').val())||0;

    if(paymentMethod==='cash' && payment<total){ alert('Pembayaran tidak cukup'); return; }

    if(!confirm('Proses pembayaran?')) return;

    $.post('{{ route("transactions.store") }}', {
        _token:'{{ csrf_token() }}',
        items: cart,
        warehouse_id: $('#warehouseSelect').val(),
        price_tier_id: $('#priceTierSelect').val(),
        discount_percentage: parseFloat($('#discountPercentage').val())||0,
        discount_amount: parseFloat($('#discountAmount').val())||0,
        voucher_code: $('#voucherCode').val(),
        payment_method: paymentMethod,
        payment_amount: paymentMethod==='cash'?payment:total
    }, function(res){
        if(res.success){
            alert('Transaksi berhasil\nNo: '+res.data.transaction_number);
            window.open('{{ url("/transactions") }}/'+res.data.transaction_id+'/print','_blank');
            cart=[]; updateCart();
            $('#voucherCode,#discountPercentage,#discountAmount,#paymentAmount').val('');
            $('#barcodeInput').focus();
        }
    }).fail(()=>alert('Terjadi kesalahan'));
}
</script>
@endpush