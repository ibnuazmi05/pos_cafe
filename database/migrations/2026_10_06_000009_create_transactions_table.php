<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('shift_id')->constrained()->restrictOnDelete();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('rounding', 12, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->enum('payment_method', ['cash', 'qris']);
            $table->decimal('paid_amount', 14, 2);
            $table->decimal('change_amount', 14, 2)->default(0);
            $table->enum('verification_status', ['pending', 'verified', 'flagged'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('note')->nullable()->comment('Catatan verifikasi admin');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['cashier_id', 'created_at']);
            $table->index('verification_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
