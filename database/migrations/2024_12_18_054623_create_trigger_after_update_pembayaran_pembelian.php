<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerAfterUpdatePembayaranPembelian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE TRIGGER `after_update_pembelian` AFTER UPDATE ON `pembelian`
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
                    CONCAT("Pembelian ID: ", OLD.id_pembelian, " diupdate. Data lama: Supplier ID = ", OLD.id_supplier, ", Data baru: Supplier ID = ", NEW.id_supplier),
                    "UPDATE",
                    OLD.id_supplier,
                    NEW.id_supplier,
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
        DB::unprepared('DROP TRIGGER IF EXISTS `after_update_pembelian`');
    }
}
