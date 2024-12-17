<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateViewPenjualanDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW view_penjualan_detail AS
            SELECT
                pd.id_penjualan_detail AS id_detail_produk,
                pd.nomor_invoice,
                pd.nama_produk,
                pd.harga_jual_produk AS harga_jual, -- Jangan menggunakan CONCAT disini
                pd.jumlah,
                (pd.jumlah * pd.harga_jual_produk) AS subtotal -- Pastikan hasilnya adalah angka
            FROM
                penjualan_detail pd
            JOIN
                penjualan p ON pd.nomor_invoice = p.nomor_invoice;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS view_penjualan_detail');
    }
}
