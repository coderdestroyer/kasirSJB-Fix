<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerAfterDeletePenjualan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE TRIGGER `after_delete_penjualan` AFTER DELETE ON `penjualan`
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
                    (SELECT name FROM users WHERE id = OLD.id_user), -- Nama pengguna dari tabel users
                    OLD.nomor_invoice, -- Nomor invoice sebagai target log
                    CONCAT("Penjualan dengan nomor invoice: ", OLD.nomor_invoice, " telah dihapus."), -- Deskripsi aktivitas
                    "DELETE", -- Jenis aktivitas
                    OLD.nomor_invoice, -- Nilai lama (yang dihapus)
                    NULL, -- Tidak ada nilai baru untuk DELETE
                    NOW(), -- Waktu dibuat
                    NOW() -- Waktu diperbarui
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
        DB::unprepared('DROP TRIGGER IF EXISTS `after_delete_penjualan`');
    }
}
