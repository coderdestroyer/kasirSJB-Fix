<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFunctionTotalKembalian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE FUNCTION hitung_kembalian(total DECIMAL(10,2), diterima DECIMAL(10,2))
            RETURNS DECIMAL(10,2)
            DETERMINISTIC
            BEGIN
                RETURN (diterima - total);
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
        DB::unprepared("DROP FUNCTION IF EXISTS hitung_kembalian");
    }
}
