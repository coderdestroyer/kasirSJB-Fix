<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProcedureStoreUpdateUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
    CREATE PROCEDURE store_user(
        IN p_name VARCHAR(255),
        IN p_email VARCHAR(255),
        IN p_password VARCHAR(255),
        IN p_foto VARCHAR(255),
        IN p_level VARCHAR(50),
        IN p_remember_token VARCHAR(100),
        IN p_profile_photo_path VARCHAR(255),
        IN p_nomor_hp VARCHAR(15),
        IN p_alamat TEXT
    )
    BEGIN
        DECLARE EXIT HANDLER FOR SQLEXCEPTION 
        BEGIN
            ROLLBACK;
        END;

        START TRANSACTION;

        -- Insert data ke tabel `user`
        INSERT INTO users (name, email, email_verified_at, password, foto, level, remember_token, profile_photo_path, created_at, updated_at)
        VALUES (
            p_name, 
            p_email, 
            NULL, 
            p_password, 
            p_foto, 
            p_level, 
            p_remember_token, 
            p_profile_photo_path, 
            NOW(), 
            NOW()
        );

        -- Ambil ID user yang baru dimasukkan
        SET @last_user_id = LAST_INSERT_ID();

        -- Insert data ke tabel `kasir`
        INSERT INTO kasir (id_user, nomor_hp, alamat, created_at, updated_at)
        VALUES (@last_user_id, p_nomor_hp, p_alamat, NOW(), NOW());

        COMMIT;
    END
');

    // Stored Procedure: update_user
    DB::unprepared('
        CREATE PROCEDURE update_user(
            IN p_id_user INT,
            IN p_name VARCHAR(255),
            IN p_email VARCHAR(255),
            IN p_password VARCHAR(255),
            IN p_foto VARCHAR(255),
            IN p_level VARCHAR(50),
            IN p_remember_token VARCHAR(100),
            IN p_profile_photo_path VARCHAR(255),
            IN p_nomor_hp VARCHAR(15),
            IN p_alamat TEXT
        )
        BEGIN
            DECLARE EXIT HANDLER FOR SQLEXCEPTION 
            BEGIN
                ROLLBACK;
            END;

            START TRANSACTION;

            -- Update data di tabel `user`
            UPDATE users 
            SET 
                name = p_name,
                email = p_email,
                password = p_password,
                foto = p_foto,
                level = p_level,
                remember_token = p_remember_token,
                profile_photo_path = p_profile_photo_path,
                updated_at = NOW()
            WHERE id = p_id_user;

            -- Update data di tabel `kasir`
            UPDATE kasir
            SET 
                nomor_hp = p_nomor_hp,
                alamat = p_alamat,
                updated_at = NOW()
            WHERE id_user = p_id_user;

            COMMIT;
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
        // Drop Stored Procedure: store_user
        DB::unprepared('DROP PROCEDURE IF EXISTS store_user');

        // Drop Stored Procedure: update_user
        DB::unprepared('DROP PROCEDURE IF EXISTS update_user');
    }
}
