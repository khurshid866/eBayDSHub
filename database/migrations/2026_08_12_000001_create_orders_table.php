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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->date('order_date');
            $table->string('ebay_order_number');
            $table->string('ebay_tracking_number')->nullable();
            $table->decimal('customer_price', 10, 2);
            $table->decimal('ad_fee_charges', 10, 2)->default(0.00);
            $table->string('amazon_order_number');
            $table->decimal('supplier_cost', 10, 2);
            $table->decimal('ebay_net', 10, 2);
            $table->decimal('profit', 10, 2);
            $table->decimal('roi', 8, 4);
            $table->enum('status', ['Pending', 'Purchased', 'Shipped', 'Delivered', 'Completed', 'Cancelled', 'Refunded'])->default('Completed');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['company_id', 'ebay_order_number']);
            $table->index(['company_id', 'order_date']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
