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

                <p>
                    <strong>Saldo Awal:</strong>
                    Rp {{ number_format($cashSession->opening_cash, 0, ',', '.') }}
                </p>

                <p>
                    <strong>Saldo Seharusnya:</strong>
                    Rp {{ number_format($expectedCash, 0, ',', '.') }}
                </p>

                @if($cashSession->status === 'open')
                    <hr>

                    <form action="{{ route('cash-sessions.close', $cashSession->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="closing_cash" class="form-label">
                                Jumlah Uang Saat Tutup Kas
                            </label>
                            <input
                                type="number"
                                name="closing_cash"
                                id="closing_cash"
                                class="form-control"
                                value="{{ old('closing_cash', $expectedCash) }}"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea
                                name="notes"
                                id="notes"
                                class="form-control"
                                rows="3"
                            >{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-lock"></i> Tutup Sesi Kas
                        </button>
                    </form>
                @else
                    <hr>

                    <p>
                        <strong>Saldo Tutup:</strong>
                        Rp {{ number_format($cashSession->closing_cash, 0, ',', '.') }}
                    </p>

                    <p>
                        <strong>Selisih:</strong>
                        Rp {{ number_format($cashSession->difference, 0, ',', '.') }}
                    </p>

                    <p class="text-muted">
                        Sesi kas ditutup pada:
                        {{ $cashSession->closed_at->format('d-m-Y H:i:s') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
