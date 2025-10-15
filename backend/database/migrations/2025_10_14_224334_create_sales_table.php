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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('status')->default('completed');
            $table->string('payment_status')->default('paid');
            $table->string('sale_type')->default('pos');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('change_due', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->decimal('cost_total', 12, 2)->default(0);
            $table->decimal('profit_total', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->text('receipt_note')->nullable();
            $table->json('payload')->nullable();
            $table->string('created_from')->nullable();
            $table->string('device_id')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
