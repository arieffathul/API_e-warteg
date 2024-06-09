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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pembeli_id');
            $table->text('tujuan');
            $table->integer('total_bayar')->default(0);
            // $table->integer('biaya_kirim')->default(0);
            $table->string('metode_bayar');
            $table->timestamps();

            $table->foreign('pembeli_id')->references('id')->on('users');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->foreign('transaction_id', 'transaction_id_fk')->references('id')->on('transactions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign('transaction_id_fk');
        });

        Schema::dropIfExists('transactions');
    }
};
