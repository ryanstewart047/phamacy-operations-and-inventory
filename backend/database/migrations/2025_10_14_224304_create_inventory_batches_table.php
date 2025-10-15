<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->date('manufactured_at')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('quantity_received');
            $table->unsignedInteger('quantity_available');
            $table->unsignedInteger('quantity_reserved')->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->string('location')->nullable();
            $table->enum('status', ['available', 'reserved', 'sold', 'expired', 'quarantined'])->default('available');
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
