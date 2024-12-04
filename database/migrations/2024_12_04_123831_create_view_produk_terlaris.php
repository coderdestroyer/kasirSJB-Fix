<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateViewProdukTerlaris extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW produk_terlaris AS
            SELECT 
                pd.nama_produk AS nama_produk,
                SUM(pd.jumlah) AS total_terjual
            FROM 
                penjualan_detail pd
            GROUP BY 
                pd.nama_produk
            ORDER BY 
                total_terjual DESC;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS produk_terlaris;");
    }
}
