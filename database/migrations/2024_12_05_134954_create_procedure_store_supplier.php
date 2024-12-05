<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProcedureStoreSupplier extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE PROCEDURE store_supplier(
                IN p_nama VARCHAR(255),
                IN p_alamat TEXT,
                IN p_telepon VARCHAR(15),
                IN p_created_at DATETIME,
                IN p_updated_at DATETIME
            )
            BEGIN
                INSERT INTO supplier (nama, alamat, telepon, created_at, updated_at)
                VALUES (p_nama, p_alamat, p_telepon, p_created_at, p_updated_at);
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
        DB::unprepared("DROP PROCEDURE IF EXISTS store_supplier;");
    }
}
