<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerUpdateUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER log_after_update_user AFTER UPDATE ON users
            FOR EACH ROW
            BEGIN
                -- Masukkan log jika ada perubahan pada kolom name atau email
                IF OLD.name <> NEW.name 
                   OR OLD.email <> NEW.email THEN
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
                        NEW.name, -- nama user terkait
                        'users', -- target log (nama tabel)
                        CONCAT('User dengan ID ', NEW.id, ' telah diperbarui.'), -- deskripsi log
                        'update', -- tipe aktivitas
                        CONCAT(
                            '{\"nama\": \"', OLD.name, '\", ',
                            '\"email\": \"', OLD.email, '\"}'
                        ), -- nilai lama dalam format JSON (sebelum update)
                        CONCAT(
                            '{\"nama\": \"', NEW.name, '\", ',
                            '\"email\": \"', NEW.email, '\"}'
                        ), -- nilai baru dalam format JSON (setelah update)
                        NOW(), -- waktu dibuat
                        NOW() -- waktu diupdate
                    );
                END IF;
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
        DB::unprepared("DROP TRIGGER IF EXISTS log_after_update_user");
    }
}
