import './pos';
document.addEventListener('keydown', function (e) {

    // hanya aktif di halaman POS
    if (!document.body.classList.contains('page-pos')) return;

    // F2 → fokus ke barcode
    if (e.key === 'F2') {
        e.preventDefault();
        document.getElementById('barcodeInput')?.focus();
        return;
    }

    // F4 → fokus ke pencarian manual
    if (e.key === 'F4') {
        e.preventDefault();
        document.getElementById('searchInput')?.focus();
        return;
    }

    // F6 → Tunai
    if (e.key === 'F6') {
        e.preventDefault();
        setPaymentMethod('cash');
        return;
    }

    // F7 → Kartu
    if (e.key === 'F7') {
        e.preventDefault();
        setPaymentMethod('card');
        return;
    }

    // F9 → E-Wallet
    if (e.key === 'F9') {
        e.preventDefault();
        setPaymentMethod('ewallet');
        return;
    }

    // F10 → Transfer
    if (e.key === 'F10') {
        e.preventDefault();
        setPaymentMethod('transfer');
        return;
    }

    // F8 → bayar
    if (e.key === 'F8') {
        e.preventDefault();
        document.getElementById('btnPay')?.click();
        return;
    }

    // ESC → clear keranjang
    if (e.key === 'Escape') {
        e.preventDefault();
        document.getElementById('btnClear')?.click();
        return;
    }
});

// helper set metode pembayaran
function setPaymentMethod(method) {
    const select = document.getElementById('paymentMethod');
    if (!select) return;

    select.value = method;
    select.dispatchEvent(new Event('change'));

    // jika tunai → fokus ke input bayar
    if (method === 'cash') {
        setTimeout(() => {
            document.getElementById('paymentAmount')?.focus();
        }, 100);
    }
}

let selectedCartIndex = -1;

document.addEventListener('keydown', function (e) {

    // aktif hanya di halaman POS
    if (!document.body.classList.contains('page-pos')) return;

    // pastikan cart sudah ada
    if (typeof cart === 'undefined') return;
    if (cart.length === 0) return;

    // jangan ganggu saat fokus input qty
    const ignoreIds = ['barcodeInput', 'searchInput', 'paymentAmount'];
    if (ignoreIds.includes(document.activeElement.id)) return;

    // ⬆️ pindah ke item atas
    if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedCartIndex = Math.max(0, selectedCartIndex - 1);
        highlightCartRow();
        return;
    }

    // ⬇️ pindah ke item bawah
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedCartIndex = Math.min(cart.length - 1, selectedCartIndex + 1);
        highlightCartRow();
        return;
    }

    // ➕ tambah qty (+, =, numpad +)
    if (e.key === '+' || e.key === '=' || e.code === 'NumpadAdd') {
        e.preventDefault();
        if (selectedCartIndex === -1) selectedCartIndex = 0;
        updateQty(selectedCartIndex, 1);
        highlightCartRow();
        return;
    }

    // ➖ kurangi qty (-, numpad -)
    if (e.key === '-' || e.code === 'NumpadSubtract') {
        e.preventDefault();
        if (selectedCartIndex === -1) selectedCartIndex = 0;
        updateQty(selectedCartIndex, -1);
        highlightCartRow();
        return;
    }

    // 🗑️ hapus item
    if (e.key === 'Delete') {
        e.preventDefault();
        if (selectedCartIndex >= 0) {
            removeItem(selectedCartIndex);
            selectedCartIndex = Math.min(selectedCartIndex, cart.length - 1);
            highlightCartRow();
        }
        return;
    }

    // ENTER → fokus ke pembayaran
    if (e.key === 'Enter') {
        e.preventDefault();
        $('#paymentAmount').focus();
    }
});

// highlight baris cart
function highlightCartRow() {
    $('#cartItems tr').removeClass('table-primary');

    if (selectedCartIndex >= 0) {
        $('#cartItems tr')
            .eq(selectedCartIndex)
            .addClass('table-primary');
    }
}


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
