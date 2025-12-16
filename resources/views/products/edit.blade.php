@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('content')
<form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header"><h5>Informasi Dasar</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Kode Produk *</label>
                            <input type="text" name="code" class="form-control" value="{{ $product->code }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Kategori *</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Nama Produk *</label>
                        <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3">{{ $product->description }}</textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Satuan *</label>
                            <input type="text" name="unit" class="form-control" value="{{ $product->unit }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Stok *</label>
                            <input type="number" name="stock" class="form-control" value="{{ $product->stock }}" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Min Stok *</label>
                            <input type="number" name="min_stock" class="form-control" value="{{ $product->min_stock }}" min="0" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-header"><h5>Harga</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Harga Beli *</label>
                            <input type="number" name="purchase_price" class="form-control" value="{{ $product->purchase_price }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Harga Jual Dasar *</label>
                            <input type="number" name="base_price" class="form-control" value="{{ $product->base_price }}" required>
                        </div>
                    </div>
                    
                    <hr><h6>Harga Bertingkat</h6>
                    @foreach($priceTiers as $tier)
                    @php $price = $product->prices->where('price_tier_id', $tier->id)->first(); @endphp
                    <div class="row mb-2">
                        <div class="col-md-6"><label>{{ $tier->name }}</label></div>
                        <div class="col-md-6">
                            <input type="number" name="prices[{{ $tier->id }}]" class="form-control" 
                                   value="{{ $price ? $price->price : 0 }}" required>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header"><h5>Status & Gambar</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ $product->status == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ $product->status == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    
                    @if($product->image)
                    <div class="mb-3">
                        <label>Gambar Saat Ini</label>
                        <img src="{{ asset('images/products/'.$product->image) }}" class="img-thumbnail w-100">
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <label>Ganti Gambar</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">Update</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary w-100">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
