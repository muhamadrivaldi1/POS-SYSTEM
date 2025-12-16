@extends('layouts.app')

@section('title', 'Produk')
@section('page-title', 'Daftar Produk')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Produk</h5>
                <a href="{{ route('products.create') }}" class="btn btn-light btn-sm">Tambah Produk</a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari produk...">
                    </div>
                    <div class="col-md-4">
                        <select id="categoryFilter" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="statusFilter" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Gambar</th><th>Kode</th><th>Nama</th><th>Kategori</th>
                            <th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>
                                @if($product->image)
                                <img src="{{ asset('images/products/'.$product->image) }}" width="50">
                                @else
                                <div class="bg-secondary text-white text-center" style="width:50px;height:50px;line-height:50px;">
                                    <i class="fas fa-image"></i>
                                </div>
                                @endif
                            </td>
                            <td>{{ $product->code }}</td>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>{{ $product->category->name }}</td>
                            <td>Rp {{ number_format($product->base_price, 0, ',', '.') }}</td>
                            <td>
                                @if($product->stock <= $product->min_stock)
                                <span class="badge bg-danger">{{ $product->stock }}</span>
                                @else
                                <span class="badge bg-success">{{ $product->stock }}</span>
                                @endif
                            </td>
                            <td>
                                @if($product->status == 'active')
                                <span class="badge bg-success">Aktif</span>
                                @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Yakin hapus?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection