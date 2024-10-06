<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('supply_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id'); // Project related to the supply order
            $table->unsignedBigInteger('contractor_id'); // The contractor who places the order
            $table->unsignedBigInteger('supplier_id'); // The supplier handling the order
            $table->unsignedBigInteger('supply_item_id'); // Reference to the supply item
            $table->integer('quantity'); // Quantity of the item
            $table->string('description')->nullable(); // Any special description for the order
            $table->date('delivery_date')->nullable(); // Expected delivery date
            $table->decimal('quoted_price', 10, 2)->nullable(); // Price quoted by the supplier
            $table->string('quote_pdf')->nullable(); // Path to the quote document (PDF)
            $table->string('delivery_form')->nullable(); // Path to the delivery form (PDF)
            $table->string('delivery_image')->nullable(); // Path to the delivery image (photo of shipped items)
            $table->string('received_image')->nullable(); // Path to the received image uploaded by the contractor
            $table->string('status')->default('pending'); // Order status: pending, approved, shipped, received, rejected
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('contractor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('supply_item_id')->references('id')->on('supply_items')->onDelete('cascade'); // New foreign key to supply_items table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_orders');
    }
};
