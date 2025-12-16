@extends('layouts.app')

@section('title', 'Tambah Pembelian')
@section('page-title', 'Tambah Pembelian')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Tambah Pembelian</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('purchases.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label">Supplier</label>
                        <input type="text" name="supplier_name" id="supplier_name" class="form-control" value="{{ old('supplier_name') }}" required>
                        @error('supplier_name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="warehouse_id" class="form-label">Gudang</label>
                            <select name="warehouse_id" id="warehouse_id" class="form-control">
                                <option value="">-- Pilih Gudang --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                     {{ $warehouse->name }} | {{ $warehouse->address }} | {{ $warehouse->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                        <label for="purchase_date" class="form-label">Tanggal Pembelian</label>
                        <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        @error('purchase_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea name="notes" id="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>

                    <hr>
                    <h6>Item Pembelian</h6>
                    <div id="items-wrapper">
                        <div class="row mb-2 item-row">
                            <div class="col-md-4">
                                <select name="items[0][product_id]" class="form-control" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->unit }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="items[0][qty]" class="form-control" placeholder="Qty" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="items[0][price]" class="form-control" placeholder="Harga" min="0" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-sm remove-item">Hapus</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-item" class="btn btn-secondary btn-sm mb-3">Tambah Item</button>

                    <div>
                        <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = 1;

document.getElementById('add-item').addEventListener('click', function() {
    let wrapper = document.getElementById('items-wrapper');
    let row = document.querySelector('.item-row').cloneNode(true);
    
    row.querySelectorAll('select, input').forEach(input => {
        input.name = input.name.replace(/\d+/, itemIndex);
        input.value = '';
    });
    
    wrapper.appendChild(row);
    itemIndex++;
});

document.addEventListener('click', function(e) {
    if(e.target && e.target.classList.contains('remove-item')){
        let rows = document.querySelectorAll('.item-row');
        if(rows.length > 1){
            e.target.closest('.item-row').remove();
        }
    }
});
</script>
@endpush
@endsection
