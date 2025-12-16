@extends('layouts.app')

@section('title', 'Sesi Kas')
@section('page-title', 'Detail Sesi Kas')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5>Detail Sesi Kas</h5>
            </div>
            <div class="card-body">
                <p><strong>User:</strong> {{ $cashSession->user->full_name }}</p>
                <p><strong>Status:</strong> {{ ucfirst($cashSession->status) }}</p>
                <p><strong>Saldo Awal:</strong> Rp {{ number_format($cashSession->opening_balance,0,',','.') }}</p>
                <p><strong>Saldo Terkini:</strong> Rp {{ number_format($expectedBalance,0,',','.') }}</p>

                @if($cashSession->status === 'open')
                    <form action="{{ route('cash-sessions.close', $cashSession->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="closing_balance" class="form-label">Jumlah Penutupan</label>
                            <input type="number" name="closing_balance" id="closing_balance" class="form-control" value="{{ $expectedBalance }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea name="notes" id="notes" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Tutup Sesi Kas</button>
                    </form>
                @else
                    <p>Sesi kas sudah ditutup pada: {{ \Carbon\Carbon::parse($cashSession->closed_at)->format('d-m-Y H:i:s') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
