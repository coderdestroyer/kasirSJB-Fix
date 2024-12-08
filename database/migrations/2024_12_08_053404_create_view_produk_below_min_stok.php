<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateViewProdukBelowMinStok extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW produk_below_min_stok AS
            SELECT 
                p.nama_produk,
                dp.stok_produk,
                dp.min_stok
            FROM 
                produk p
            JOIN 
                detail_produk dp ON p.kode_produk = dp.kode_produk
            WHERE 
                dp.stok_produk < dp.min_stok
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS produk_below_min_stok");
    }
}
