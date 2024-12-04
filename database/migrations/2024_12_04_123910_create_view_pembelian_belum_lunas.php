<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateViewPembelianBelumLunas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW pembelian_belum_lunas AS
            SELECT 
                p.id_pembelian AS id_pembelian,
                s.nama AS nama,
                SUM(pd.harga_beli_produk * pd.jumlah) AS total_hutang
            FROM 
                pembelian_detail pd
            JOIN 
                pembelian p ON pd.id_pembelian = p.id_pembelian
            JOIN 
                supplier s ON p.id_supplier = s.id_supplier
            WHERE 
                pd.status = 'belum lunas'
            GROUP BY 
                p.id_pembelian, 
                s.nama;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS pembelian_belum_lunas;");
    }
}
