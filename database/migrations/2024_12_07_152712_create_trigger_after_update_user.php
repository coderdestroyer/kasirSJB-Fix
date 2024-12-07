<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerLogAfterUpdateUser extends Migration
{
    public function up()
    {
        DB::unprepared('
            DELIMITER $$

            CREATE TRIGGER log_after_update_user
            AFTER UPDATE ON users
            FOR EACH ROW
            BEGIN
                DECLARE kasir_alamat TEXT DEFAULT "Tidak ditemukan";
                DECLARE kasir_nomor_hp TEXT DEFAULT "Tidak ditemukan";

                -- Ambil data alamat dan nomor_hp dari tabel kasir berdasarkan NEW.name
                BEGIN
                    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
                        SET kasir_alamat = "Gagal ambil data", kasir_nomor_hp = "Gagal ambil data";

                    -- Ambil data alamat dan nomor_hp berdasarkan NEW.name
                    SELECT alamat, nomor_hp INTO kasir_alamat, kasir_nomor_hp
                    FROM kasir
                    WHERE kasir.name = NEW.name
                    LIMIT 1;
                END;

                -- Masukkan data ke log_activity hanya jika ada perubahan pada kolom yang relevan (name atau email)
                IF OLD.name <> NEW.name OR OLD.email <> NEW.email THEN
                    -- Masukkan data ke log_activity
                    BEGIN
                        DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
                            SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Error saat menyimpan data ke log_activity";

                        -- Masukkan data ke log_activity
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
                            NEW.name, -- nama user yang diupdate
                            "users", -- target log (nama tabel)
                            CONCAT("User dengan ID ", NEW.id, " telah diperbarui."), -- deskripsi log
                            "update", -- tipe aktivitas
                            CONCAT(
                                \'{"nama": "\', OLD.name, \'", \', 
                                \'"email": "\', OLD.email, \'" }\'
                            ), -- nilai lama dalam format JSON (sebelum update)
                            CONCAT(
                                \'{"nama": "\', NEW.name, \'", \', 
                                \'"email": "\', NEW.email, \'", \', 
                                \'"alamat": "\', kasir_alamat, \'", \', 
                                \'"nomor_hp": "\', kasir_nomor_hp, \'" }\'
                            ), -- nilai baru dalam format JSON (setelah update)
                            NOW(), -- waktu dibuat
                            NOW() -- waktu diupdate
                        );
                    END;
                END IF;

            END $$

            DELIMITER ;
        ');
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS log_after_update_user');
    }
}

