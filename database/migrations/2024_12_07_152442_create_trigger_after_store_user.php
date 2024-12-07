<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerOnInsertUsers extends Migration
{
    public function up()
    {
        DB::unprepared('
            DELIMITER $$

            CREATE TRIGGER after_user_insert
            AFTER INSERT ON users
            FOR EACH ROW
            BEGIN
                DECLARE kasir_alamat TEXT DEFAULT "Tidak ditemukan";
                DECLARE kasir_nomor_hp TEXT DEFAULT "Tidak ditemukan";

                -- Cek jika ada masalah saat mengambil data dari tabel kasir
                BEGIN
                    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
                        SET kasir_alamat = "Gagal ambil data", kasir_nomor_hp = "Gagal ambil data";

                    SELECT alamat, nomor_hp INTO kasir_alamat, kasir_nomor_hp
                    FROM kasir
                    WHERE kasir.name = NEW.name
                    LIMIT 1;
                END;

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
                        new_value, 
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        NOW(), 
                        NEW.name, 
                        "users", 
                        CONCAT("User baru dengan ID ", NEW.id, " ditambahkan."),
                        "insert", 
                        CONCAT(
                            \'{"nama": "\', NEW.name, \'", \', 
                            \'"email": "\', NEW.email, \'", \', 
                            \'"alamat": "\', kasir_alamat, \'", \', 
                            \'"nomor_hp": "\', kasir_nomor_hp, \'" }\'
                        ), 
                        NOW(), 
                        NOW()
                    );
                END;
            END $$

            DELIMITER ;
        ');
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_user_insert');
    }
}
