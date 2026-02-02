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
        Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->string('transaction_number')->unique();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('warehouse_id')->nullable()->constrained();
    $table->foreignId('price_tier_id')->nullable()->constrained();

    $table->date('business_date')->index();
    $table->dateTime('transaction_date');

    $table->decimal('subtotal', 15, 2)->default(0);
    $table->decimal('discount_percentage', 5, 2)->default(0);
    $table->decimal('discount_amount', 15, 2)->default(0);

    $table->decimal('tax_percentage', 5, 2)->default(0);
    $table->decimal('tax_amount', 15, 2)->default(0);

    $table->decimal('total', 15, 2)->default(0);

    $table->enum('payment_method', ['cash','card','ewallet','transfer']);
    $table->decimal('payment_amount', 15, 2)->default(0);
    $table->decimal('change_amount', 15, 2)->default(0);

    $table->boolean('allow_negative_stock')->default(false);
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

    $table->enum('status', ['pending','completed','cancelled','returned'])->default('completed');
    $table->text('notes')->nullable();

    $table->foreignId('cash_session_id')->nullable()
        ->constrained('cash_sessions')->nullOnDelete();

    $table->timestamps();
    $table->softDeletes();
    });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
