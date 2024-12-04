<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerAfterInsertOnPembelian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER after_insert_pembelian
            AFTER INSERT ON pembelian
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
                    NOW(), -- Waktu aktivitas dicatat
                    (SELECT nama FROM supplier WHERE id_supplier = NEW.id_supplier), -- Nama supplier (jika tabel supplier ada)
                    NEW.id_pembelian, -- ID pembelian sebagai target log
                    CONCAT('Pembelian baru dibuat dengan ID pembelian: ', NEW.id_pembelian, ' oleh supplier ID: ', NEW.id_supplier), -- Deskripsi log
                    'INSERT', -- Jenis aktivitas (INSERT)
                    NULL, -- Tidak ada nilai lama karena ini operasi INSERT
                    NEW.id_pembelian, -- Nilai baru yang dimasukkan
                    NOW(), -- Waktu dibuat
                    NOW()  -- Waktu diperbarui
                );
            END
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS after_insert_pembelian");
    }
}
