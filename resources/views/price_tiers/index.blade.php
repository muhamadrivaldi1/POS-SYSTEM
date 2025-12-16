@extends('layouts.app')

@section('title', 'Harga Bertingkat')
@section('page-title', 'Daftar Harga Bertingkat')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Harga Bertingkat</h5>
                <a href="{{ route('price-tiers.create') }}" class="btn btn-light text-primary">
                    <i class="fas fa-plus-circle"></i> Tambah Harga
                </a>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($priceTiers as $tier)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $tier->name }}</td>
                                <td>{{ $tier->description ?? '-' }}</td>
                                <td>{{ $tier->priority }}</td>
                                <td>
                                    @if($tier->status == 'active')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('price-tiers.edit', $tier->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('price-tiers.destroy', $tier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus harga bertingkat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada harga bertingkat</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $priceTiers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
