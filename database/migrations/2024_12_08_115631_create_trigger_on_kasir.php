<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerOnKasir extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER log_after_update_kasir AFTER UPDATE ON kasir
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
                    NOW(), -- waktu log
                    (SELECT name FROM users WHERE id = NEW.id_user), -- nama user terkait (diambil dari tabel users)
                    'kasir', -- target log (nama tabel)
                    CONCAT('Kasir dengan ID ', NEW.id_kasir, ' telah diperbarui.'), -- deskripsi log
                    'update', -- tipe aktivitas
                    CONCAT(
                        '{\"nomor_hp\": \"', OLD.nomor_hp, '\", ',
                        '\"alamat\": \"', OLD.alamat, '\"}'
                    ), -- nilai lama dalam format JSON (sebelum update)
                    CONCAT(
                        '{\"nomor_hp\": \"', NEW.nomor_hp, '\", ',
                        '\"alamat\": \"', NEW.alamat, '\"}'
                    ), -- nilai baru dalam format JSON (setelah update)
                    NOW(), -- waktu dibuat
                    NOW() -- waktu diupdate
                );
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
        DB::unprepared("DROP TRIGGER IF EXISTS log_after_update_kasir");
    }
}
