<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCartPembelian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_pembelian', function (Blueprint $table) {
            $table->id('id_cart_pembelian');
            $table->integer('id_supplier');
            $table->integer('id_pembelian');
            $table->string('kode_produk');
            $table->integer('jumlah')->default(1);
            $table->string('uom')->default('pieces');
            $table->timestamps(0); // creates created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_pembelian');
    }
}
