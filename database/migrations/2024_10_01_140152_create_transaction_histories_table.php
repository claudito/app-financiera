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
        Schema::create('transaction_histories', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', $precision = 8, $scale = 2);
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->unsignedBigInteger('account_id');
            $table->foreign('type_deposit_id')->references('id')->on('type_deposits');
            $table->unsignedBigInteger('type_deposit_id');
            $table->dateTime('transaction_date');
            $table->string('fee')->nullable();
            $table->decimal('amount_fee', $precision = 8, $scale = 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_histories');
    }
};
