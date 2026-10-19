<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['in', 'out', 'opname', 'sale', 'void_return']);
            $table->decimal('quantity', 14, 3)->comment('Bertanda: + masuk, - keluar');
            $table->decimal('stock_after', 14, 3);
            $table->nullableMorphs('reference');
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['ingredient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
