<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViewProdukTerjualHarian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW view_produk_terjual_harian AS
            SELECT 
                DATE(pd.created_at) AS tanggal, -- Tanggal penjualan
                p.nama_produk, -- Nama produk
                dp.merk, -- Merk produk
                p.harga_jual AS harga_jual_produk, -- Harga jual produk
                SUM(pd.jumlah) AS total_terjual, -- Total jumlah produk terjual
                (p.harga_jual * SUM(pd.jumlah)) AS total_pendapatan -- Pendapatan total (harga jual x jumlah terjual)
            FROM penjualan_detail pd
            INNER JOIN produk p ON pd.nama_produk = p.nama_produk -- Relasi tabel produk dengan penjualan detail
            INNER JOIN detail_produk dp ON dp.id_detail_produk = p.id_kategori -- Relasi dengan detail_produk
            GROUP BY 
                DATE(pd.created_at), 
                p.nama_produk, 
                dp.merk, 
                p.harga_jual -- Kelompokkan berdasarkan tanggal, produk, merk, dan harga jual
            ORDER BY 
                tanggal ASC, 
                p.nama_produk ASC; -- Urutkan berdasarkan tanggal dan nama produk
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS view_produk_terjual_harian');
    }
}
