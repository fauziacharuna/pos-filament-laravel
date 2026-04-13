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
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->decimal('total_price', 10, 2);
            $table->date('date');
             $table->enum('status', ['new', 'processing', 'cancelled','completed'])->default('new')->after('total_price');
            $table->integer('discount')->default(0)->after('status');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount');
            $table->decimal('total_payment', 10, 2)->default(0)->after('discount_amount');
            $table->timestamps();
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
