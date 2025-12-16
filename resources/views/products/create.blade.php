@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk Baru')

@section('content')
<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header"><h5>Informasi Dasar</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Kode Produk *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                   value="{{ old('code') }}" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Kategori *</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Nama Produk *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Satuan *</label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit', 'pcs') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Stok Awal *</label>
                            <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Min Stok *</label>
                            <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', 5) }}" min="0" required>
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
                            <input type="number" name="purchase_price" class="form-control" value="0" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Harga Jual Dasar *</label>
                            <input type="number" name="base_price" class="form-control" value="0" min="0" required>
                        </div>
                    </div>
                    
                    <hr><h6>Harga Bertingkat</h6>
                    @foreach($priceTiers as $tier)
                    <div class="row mb-2">
                        <div class="col-md-6"><label>{{ $tier->name }}</label></div>
                        <div class="col-md-6">
                            <input type="number" name="prices[{{ $tier->id }}]" class="form-control" value="0" min="0" required>
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
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label>Gambar Produk</label>
                        <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                        <small class="text-muted">Max 2MB</small>
                    </div>
                    
                    <div id="imagePreview" style="display:none;">
                        <img src="" alt="Preview" class="img-thumbnail w-100">
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">Simpan</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary w-100">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
$('#imageInput').on('change', function(e) {
    let file = e.target.files[0];
    if (file) {
        let reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview img').attr('src', e.target.result);
            $('#imagePreview').show();
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection