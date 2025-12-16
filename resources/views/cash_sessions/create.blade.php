@extends('layouts.app')

@section('title', 'Buka Sesi Kas')
@section('page-title', 'Buka Sesi Kas')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Buka Sesi Kas</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('cash-sessions.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="opening_balance" class="form-label">Saldo Awal</label>
                        <input type="number" name="opening_balance" id="opening_balance" class="form-control" value="{{ old('opening_balance', 0) }}" required>
                        @error('opening_balance')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success">Buka Sesi Kas</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
