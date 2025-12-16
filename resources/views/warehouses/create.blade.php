@extends('layouts.app')
@section('title', 'Tambah Gudang')
@section('page-title', 'Tambah Gudang')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('warehouses.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Nama Gudang *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" placeholder="Contoh: Gudang Utama" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Alamat lengkap gudang">{{ old('address') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Telepon</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Nomor telepon">
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection