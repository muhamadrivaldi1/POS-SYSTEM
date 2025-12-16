<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Struk</title>
<style>
body{font-family:'Courier New',monospace;font-size:12px;padding:10px;max-width:300px;margin:0 auto;}
.header{text-align:center;margin-bottom:10px;border-bottom:1px dashed #000;padding-bottom:10px;}
.item{margin-bottom:5px;}
.total{border-top:1px solid #000;padding-top:10px;font-weight:bold;font-size:14px;}
</style></head><body>
<div class="header">
    <h2>TOKO ANDA</h2>
    <p>Jl. Contoh No. 123</p>
</div>
<p><strong>No:</strong> {{ $transaction->transaction_number }}</p>
<p><strong>Tanggal:</strong> {{ $transaction->transaction_date->format('d/m/Y H:i') }}</p>
<p><strong>Kasir:</strong> {{ $transaction->user->full_name }}</p>
<hr>
@foreach($transaction->details as $detail)
<div class="item">
    <strong>{{ $detail->product_name }}</strong><br>
    {{ $detail->qty }} x Rp {{ number_format($detail->price, 0, ',', '.') }} = 
    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
</div>
@endforeach
<hr>
<p>Subtotal: Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</p>
@if($transaction->discount_amount > 0)
<p>Diskon: - Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</p>
@endif
<p class="total">TOTAL: Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
<p>Bayar: Rp {{ number_format($transaction->payment_amount, 0, ',', '.') }}</p>
@if($transaction->change_amount > 0)
<p>Kembali: Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</p>
@endif
<div style="text-align:center;margin-top:20px;">
    <p>Terima Kasih</p>
</div>
<script>window.print();</script>
</body></html>
