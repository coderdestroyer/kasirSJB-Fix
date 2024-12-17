<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateViewPembelianDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW view_pembelian_detail AS
            SELECT 
                pembelian_detail.id_pembelian_detail,
                pembelian_detail.id_pembelian,
                pembelian_detail.nama_produk,
                pembelian_detail.harga_beli_produk,
                pembelian_detail.jumlah,
                pembelian_detail.status,
                hitung_harga_beli_produk(pembelian_detail.id_pembelian_detail) AS subtotal
            FROM 
                pembelian_detail
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS view_pembelian_detail");
    }
}
