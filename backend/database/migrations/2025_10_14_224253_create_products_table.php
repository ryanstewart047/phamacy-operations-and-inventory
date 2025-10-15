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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->text('description')->nullable();
            $table->string('unit_of_measure')->default('unit');
            $table->unsignedInteger('pack_size')->default(1);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->unsignedInteger('reorder_level')->default(0);
            $table->unsignedInteger('reorder_quantity')->default(0);
            $table->boolean('track_batches')->default(true);
            $table->boolean('track_serial_numbers')->default(false);
            $table->boolean('expiry_required')->default(true);
            $table->boolean('is_prescription_only')->default(false);
            $table->boolean('is_controlled_substance')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('storage_instructions')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('last_inventory_count_at')->nullable();
            $table->timestamp('low_stock_notified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
