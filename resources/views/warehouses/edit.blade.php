@extends('layouts.app')
@section('title', 'Edit Gudang')
@section('page-title', 'Edit Gudang')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('warehouses.update', $warehouse) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label>Nama Gudang *</label>
                        <input type="text" name="name" class="form-control" value="{{ $warehouse->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control" rows="3">{{ $warehouse->address }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Telepon</label>
                        <input type="text" name="phone" class="form-control" value="{{ $warehouse->phone }}">
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ $warehouse->status == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ $warehouse->status == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
