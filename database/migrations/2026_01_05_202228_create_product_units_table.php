<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // produk induk
            $table->string('name');       // nama satuan, contoh: Ball, Slof, Bungkus
            $table->integer('conversion')->default(1); // jumlah terkecil dalam satuan ini, contoh Ball=1000 batang
            $table->decimal('price', 15, 2)->nullable(); // harga optional per satuan
            $table->integer('min_stock')->default(0); // stok minimal untuk satuan ini
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_units');
    }
};
