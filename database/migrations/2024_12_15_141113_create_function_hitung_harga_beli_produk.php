<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFunctionHitungHargaBeliProduk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_harga_beli_produk` (`p_id_pembelian_detail` INT) 
        RETURNS DECIMAL(10,2) DETERMINISTIC 
        BEGIN
            DECLARE total_harga DECIMAL(10, 2);

            SELECT SUM(harga_beli_produk * jumlah)
            INTO total_harga
            FROM pembelian_detail
            WHERE id_pembelian_detail = p_id_pembelian_detail;

            RETURN IFNULL(total_harga, 0);
        END
    ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS hitung_harga_beli_produk');
    }
}
