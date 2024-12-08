<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateViewTransaksiTerbaru extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE VIEW transaksi_barusan AS
            SELECT 
                p.updated_at AS tanggal_transaksi,
                p.nomor_invoice AS nomor_invoice,
                'Penjualan' AS tipe_transaksi,
                SUM((pd.jumlah * pd.harga_jual_produk)) AS total_pembayaran
            FROM 
                penjualan p
            JOIN 
                penjualan_detail pd 
            ON 
                (p.nomor_invoice = pd.nomor_invoice)
            GROUP BY 
                p.updated_at, p.nomor_invoice

            UNION ALL

            SELECT 
                b.updated_at AS tanggal_transaksi,
                b.id_pembelian AS nomor_invoice,
                'Pembelian' AS tipe_transaksi,
                SUM((bd.jumlah * bd.harga_beli_produk)) AS total_pembayaran
            FROM 
                pembelian b
            JOIN 
                pembelian_detail bd 
            ON 
                (b.id_pembelian = bd.id_pembelian)
            GROUP BY 
                b.updated_at, b.id_pembelian

            ORDER BY 
                tanggal_transaksi DESC;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP VIEW IF EXISTS transaksi_barusan");
    }
}
