<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProcedureUpdateKategori extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE PROCEDURE update_kategori(IN kategori_id INT, IN nama_kategori VARCHAR(255))
            BEGIN
                UPDATE kategori
                SET nama_kategori = nama_kategori, updated_at = NOW()
                WHERE id_kategori = kategori_id;
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
        DB::unprepared("DROP PROCEDURE IF EXISTS update_kategori;");
    }
}
