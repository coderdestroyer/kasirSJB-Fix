<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFunctionTotalHarga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE FUNCTION `total_harga`(p_nomor_invoice INT) 
            RETURNS DECIMAL(10,2)
            DETERMINISTIC
            BEGIN
                RETURN (
                    SELECT COALESCE(SUM(harga_jual_produk * jumlah), 0) 
                    FROM penjualan_detail 
                    WHERE nomor_invoice = p_nomor_invoice
                );
            END;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS total_harga");
    }
}
