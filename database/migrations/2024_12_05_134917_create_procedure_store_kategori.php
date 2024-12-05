<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProcedureStoreKategori extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
        CREATE PROCEDURE store_kategori(IN nama_kategori VARCHAR(255))
        BEGIN
            INSERT INTO kategori (nama_kategori, created_at, updated_at) 
            VALUES (nama_kategori, NOW(), NOW());
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
        DB::unprepared("DROP PROCEDURE IF EXISTS store_kategori;");
    }
}
