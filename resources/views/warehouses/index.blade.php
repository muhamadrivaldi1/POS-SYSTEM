@extends('layouts.app')
@section('title', 'Gudang')
@section('page-title', 'Daftar Gudang')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gudang</h5>
                <a href="{{ route('warehouses.create') }}" class="btn btn-light btn-sm">Tambah Gudang</a>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead><tr><th>No.</th><th>Nama</th><th>Alamat</th><th>Telepon</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $warehouse->name }}</td>
                            <td>{{ $warehouse->address ?? '-' }}</td>
                            <td>{{ $warehouse->phone ?? '-' }}</td>
                            <td>
                                @if($warehouse->status == 'active')
                                <span class="badge bg-success">Aktif</span>
                                @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">{{ $warehouses->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
