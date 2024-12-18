<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFunctionConvertUom extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE FUNCTION convert_uom(quantity INT, uom VARCHAR(20))
            RETURNS INT
            DETERMINISTIC
            BEGIN
                DECLARE result INT;
                IF uom = "pieces" THEN
                    SET result = quantity;
                ELSEIF uom = "dus" THEN
                    SET result = quantity * 50;
                ELSEIF uom = "roll" THEN
                    SET result = quantity * 100;
                ELSE
                    SET result = 0;
                END IF;
                RETURN result;
            END ;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS convert_uom');
    }
}
