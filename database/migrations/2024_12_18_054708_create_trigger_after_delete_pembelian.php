<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerAfterDeletePembelian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE TRIGGER `after_delete_pembelian` AFTER DELETE ON `pembelian`
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activity (
                    log_time,
                    name,
                    log_target,
                    log_description,
                    activity_type,
                    old_value,
                    new_value,
                    created_at,
                    updated_at
                )
                VALUES (
                    NOW(),
                    (SELECT nama FROM supplier WHERE id_supplier = OLD.id_supplier),
                    OLD.id_pembelian,
                    CONCAT("Pembelian dengan ID: ", OLD.id_pembelian, " telah dihapus."),
                    "DELETE",
                    OLD.id_supplier,
                    NULL,
                    NOW(),
                    NOW()
                );
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
        DB::unprepared('DROP TRIGGER IF EXISTS `after_delete_pembelian`');
    }
}
