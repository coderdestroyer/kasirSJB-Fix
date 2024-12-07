<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKolomUomMinStok extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detail_produk', function (Blueprint $table) {
            $table->integer('min_stok')->default(0)->after('stok_produk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detail_produk', function (Blueprint $table) {
            $table->dropColumn(['min_stok']);
        });
    }
}
