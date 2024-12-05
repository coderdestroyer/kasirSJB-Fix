<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProcedureUpdateSupplier extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE PROCEDURE update_supplier(
                IN p_id_supplier INT,
                IN p_nama VARCHAR(255),
                IN p_alamat TEXT,
                IN p_telepon VARCHAR(15),
                IN p_updated_at DATETIME
            )
            BEGIN
                UPDATE supplier
                SET 
                    nama = p_nama,
                    alamat = p_alamat,
                    telepon = p_telepon,
                    updated_at = p_updated_at
                WHERE id_supplier = p_id_supplier;
            END ;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS update_supplier;");
    }
}
