@extends('layouts.app')

@section('title', 'Return')
@section('page-title', 'Daftar Return')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Return</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>No. Return</th>
                            <th>Transaksi</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Tanggal Return</th>
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'supervisor')
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $return->return_number }}</td>
                                <td>{{ $return->transaction->transaction_number ?? '-' }}</td>
                                <td>{{ $return->user->full_name ?? '-' }}</td>
                                <td>{{ ucfirst($return->status) }}</td>
                                <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y H:i:s') }}</td>

                                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'supervisor')
                                    <td>
                                        @if($return->status === 'pending')
                                            <form action="{{ route('returns.approve', $return->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form action="{{ route('returns.reject', $return->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        @else
                                            <span class="text-muted">Sudah diproses</span>
                                        @endif
                                    </td>
                                @endif

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data return</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $returns->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
