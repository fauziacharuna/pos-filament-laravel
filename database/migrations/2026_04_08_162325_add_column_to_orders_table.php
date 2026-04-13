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
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->enum('payment_status', ['paid', 'unpaid', 'failed'])->default('unpaid')->after('total_payment');
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'mobile_payment'])->nullable()->after('payment_status');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->dateTime('paid_at')->nullable()->after('payment_reference');
            $table->string('notes')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
