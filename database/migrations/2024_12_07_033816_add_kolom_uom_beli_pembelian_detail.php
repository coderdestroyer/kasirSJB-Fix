<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKolomUomBeliPembelianDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pembelian_detail', function (Blueprint $table) {
            $table->string('uom_beli', 50)->nullable()->after('jumlah');
            $table->float('konversi_uom', 8, 2)->default(1)->after('uom_beli');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pembelian_detail', function (Blueprint $table) {
            $table->dropColumn(['uom_beli', 'konversi_uom']);
        });
    }
}
