@extends('layouts.app')

@section('title', 'Sesi Kas')
@section('page-title', 'Daftar Sesi Kas')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Sesi Kas</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Jumlah Awal</th>
                            <th>Jumlah Akhir</th>
                            <th>Dibuka Pada</th>
                            <th>Ditutup Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr>
                                <td>{{ $sessions->firstItem() + $loop->index }}</td>
                                <td>{{ $session->user->full_name ?? '-' }}</td>
                                <td>
                                    @if($session->status === 'open')
                                        <span class="badge bg-success">Open</span>
                                    @else
                                        <span class="badge bg-secondary">Closed</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($session->opening_balance ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($session->closing_balance ?? 0, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($session->opened_at)->format('d-m-Y H:i:s') }}</td>
                                <td>{{ $session->closed_at ? \Carbon\Carbon::parse($session->closed_at)->format('d-m-Y H:i:s') : '-' }}</td>
                                <td>
                                    <a href="{{ route('cash-sessions.show', $session->id) }}" class="btn btn-sm btn-primary">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada sesi kas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $sessions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
