<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerAfterInsertOnPenjualan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
        CREATE TRIGGER after_insert_penjualan
        AFTER INSERT ON penjualan
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
                NOW(), -- Waktu log
                (SELECT name FROM users WHERE id = NEW.id_user), -- Nama pengguna dari tabel users
                NEW.nomor_invoice, -- Nomor invoice sebagai target log
                CONCAT('Penjualan baru dibuat dengan nomor invoice: ', NEW.nomor_invoice), -- Deskripsi aktivitas
                'INSERT', -- Jenis aktivitas
                NULL, -- Tidak ada nilai lama untuk INSERT
                NEW.nomor_invoice, -- Nilai baru yang ditambahkan
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
        DB::unprepared("DROP TRIGGER IF EXISTS after_insert_penjualan");
    }
}
