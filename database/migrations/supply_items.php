<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('supply_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id'); // Link to the supplier who added the item
            $table->unsignedInteger('supplier_item_number'); 
            $table->string('name'); // Name of the supply item
            $table->text('description')->nullable(); // Description of the item
            $table->decimal('price', 10, 2); // Price of the item
            $table->integer('stock_quantity')->default(0); // Stock quantity of the item
            $table->timestamps();

            // Foreign key to reference suppliers from users table
            $table->foreign('supplier_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_items');
    }
};
