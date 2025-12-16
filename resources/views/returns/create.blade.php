@extends('layouts.app')

@section('title', 'Buat Return')
@section('page-title', 'Buat Return')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Buat Return untuk Transaksi #{{ $transaction->transaction_number }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('returns.store') }}" method="POST">
            @csrf
            <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">

            <div class="mb-3">
                <label for="reason" class="form-label">Catatan / Alasan Return</label>
                <textarea name="reason" id="reason" class="form-control" rows="3" required>{{ old('reason') }}</textarea>
            </div>

            <h6>Produk yang akan direturn</h6>
            <table class="table table-sm mb-3">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty Terjual</th>
                        <th>Qty Return</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->details as $detail)
                    <tr>
                        <td>{{ $detail->product_name }}</td>
                        <td>{{ $detail->qty }}</td>
                        <td>
                            <input type="number" name="items[{{ $detail->id }}][qty]" 
                                   max="{{ $detail->qty }}" min="0" value="0" class="form-control">
                            <input type="hidden" name="items[{{ $detail->id }}][product_id]" value="{{ $detail->product_id }}">
                            <input type="hidden" name="items[{{ $detail->id }}][price]" value="{{ $detail->price }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary">Simpan Return</button>
            <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
